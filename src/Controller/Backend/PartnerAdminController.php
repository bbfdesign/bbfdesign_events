<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Controller\Backend;

use JTL\DB\DbInterface;
use JTL\Smarty\JTLSmarty;
use Plugin\bbfdesign_events\src\Config\EventConfig;
use Plugin\bbfdesign_events\src\Helper\SlugHelper;

class PartnerAdminController
{
    public function __construct(
        private readonly DbInterface $db,
        private readonly JTLSmarty $smarty,
        private readonly string $postURL
    ) {}

    public function dispatch(string $action): void
    {
        match ($action) {
            'create' => $this->prepareForm(0),
            'edit' => $this->prepareForm((int) ($_GET['id'] ?? 0)),
            'save' => $this->handleSave(),
            'delete' => $this->handleDelete(),
            default => $this->prepareList(),
        };
    }

    private function prepareList(): void
    {
        $partners = $this->db->getObjects(
            'SELECT p.*, pt.name FROM bbf_partners p
             LEFT JOIN bbf_partners_translation pt ON p.id = pt.partner_id AND pt.language_iso = :lang
             ORDER BY p.sort_order, p.id',
            ['lang' => EventConfig::DEFAULT_LANGUAGE]
        );
        $this->smarty->assign('partners', $partners);
    }

    private function prepareForm(int $id): void
    {
        $partner = null;
        $translations = [];
        $assignedCatIds = [];

        if ($id > 0) {
            $partner = $this->db->getSingleObject('SELECT * FROM bbf_partners WHERE id = :id', ['id' => $id]);
            if ($partner === null) {
                header('Location: ' . $this->buildUrl('partners') . '&error=notfound');
                exit;
            }
            $translations = $this->db->getObjects('SELECT * FROM bbf_partners_translation WHERE partner_id = :pid', ['pid' => $id]);
            $assigned = $this->db->getObjects('SELECT category_id FROM bbf_partner_category_mapping WHERE partner_id = :pid', ['pid' => $id]);
            $assignedCatIds = array_map(fn($r) => (int) $r->category_id, $assigned);
        }

        $categories = $this->db->getObjects(
            'SELECT pc.*, pct.name FROM bbf_partner_categories pc
             LEFT JOIN bbf_partner_categories_translation pct ON pc.id = pct.category_id AND pct.language_iso = :lang
             ORDER BY pc.sort_order',
            ['lang' => EventConfig::DEFAULT_LANGUAGE]
        );

        $this->smarty->assign('partner', $partner);
        $this->smarty->assign('translations', $translations);
        $this->smarty->assign('categories', $categories);
        $this->smarty->assign('assignedCatIds', $assignedCatIds);
        $this->smarty->assign('languages', $this->getLanguages());
        $this->smarty->assign('isEdit', $id > 0);
        $this->smarty->assign('activePage', 'partner_edit');
    }

    private function handleSave(): void
    {
        $id = (int) ($_POST['partner_id'] ?? 0);
        $isNew = $id === 0;

        $firstName = $_POST['trans_ger_name'] ?? '';
        $slug = ($_POST['slug'] ?? '') !== '' ? $_POST['slug'] : SlugHelper::generate($firstName);
        $slug = SlugHelper::ensureUnique($slug, function (string $s) use ($id, $isNew) {
            $exclude = $isNew ? '' : ' AND id != :eid';
            $params = ['slug' => $s];
            if (!$isNew) { $params['eid'] = $id; }
            $r = $this->db->getSingleObject("SELECT COUNT(*) as cnt FROM bbf_partners WHERE slug = :slug{$exclude}", $params);
            return (int) ($r->cnt ?? 0) > 0;
        });

        $data = (object) [
            'slug' => $slug,
            'logo' => ($_POST['logo'] ?? '') !== '' ? $_POST['logo'] : null,
            'logo_dark' => ($_POST['logo_dark'] ?? '') !== '' ? $_POST['logo_dark'] : null,
            'website_url' => ($_POST['website_url'] ?? '') !== '' ? $_POST['website_url'] : null,
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'sort_order' => (int) ($_POST['sort_order'] ?? 0),
        ];

        if ($isNew) {
            $id = $this->db->insert('bbf_partners', $data);
        } else {
            $this->db->update('bbf_partners', 'id', $id, $data);
        }

        foreach ($this->getLanguages() as $lang) {
            $iso = $lang->iso;
            $name = $_POST['trans_' . $iso . '_name'] ?? '';
            if ($name === '') { continue; }

            $tData = (object) [
                'partner_id' => $id, 'language_iso' => $iso,
                'name' => trim($name),
                'short_desc' => ($_POST['trans_' . $iso . '_short_desc'] ?? '') !== '' ? $_POST['trans_' . $iso . '_short_desc'] : null,
                'long_desc' => ($_POST['trans_' . $iso . '_long_desc'] ?? '') !== '' ? $_POST['trans_' . $iso . '_long_desc'] : null,
                'cta_label' => ($_POST['trans_' . $iso . '_cta_label'] ?? '') !== '' ? $_POST['trans_' . $iso . '_cta_label'] : null,
                'cta_url' => ($_POST['trans_' . $iso . '_cta_url'] ?? '') !== '' ? $_POST['trans_' . $iso . '_cta_url'] : null,
            ];
            $existing = $this->db->getSingleObject('SELECT id FROM bbf_partners_translation WHERE partner_id = :pid AND language_iso = :lang', ['pid' => $id, 'lang' => $iso]);
            if ($existing) {
                $this->db->update('bbf_partners_translation', 'id', (int) $existing->id, $tData);
            } else {
                $this->db->insert('bbf_partners_translation', $tData);
            }
        }

        $this->db->executeQuery('DELETE FROM bbf_partner_category_mapping WHERE partner_id = :pid', ['pid' => $id]);
        foreach ($_POST['categories'] ?? [] as $catId) {
            $this->db->insert('bbf_partner_category_mapping', (object) ['partner_id' => $id, 'category_id' => (int) $catId]);
        }

        header('Location: ' . $this->buildUrl('partners', 'edit', $id) . '&msg=' . ($isNew ? 'created' : 'updated'));
        exit;
    }

    private function handleDelete(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id > 0) { $this->db->delete('bbf_partners', 'id', $id); }
        header('Location: ' . $this->buildUrl('partners') . '&msg=deleted');
        exit;
    }

    private function getLanguages(): array
    {
        $rows = $this->db->getObjects("SELECT cISO as iso, cNameDeutsch as name FROM tsprache WHERE active = 1 ORDER BY cISO");
        return $rows ?: [(object) ['iso' => 'ger', 'name' => 'Deutsch']];
    }

    private function buildUrl(string $page, string $action = 'list', ?int $id = null): string
    {
        $url = $this->postURL . '&bbf_page=' . $page;
        if ($action !== 'list') { $url .= '&action=' . $action; }
        if ($id !== null) { $url .= '&id=' . $id; }
        return $url;
    }
}
