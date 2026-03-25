<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Controller\Backend;

use JTL\DB\DbInterface;
use JTL\Shop;
use Plugin\bbfdesign_events\src\Config\EventConfig;
use Plugin\bbfdesign_events\src\Helper\SlugHelper;

class PartnerAdminController
{
    private DbInterface $db;
    private string $templatePath;

    public function __construct()
    {
        $this->db = Shop::Container()->getDB();
        $this->templatePath = EventConfig::getPluginPath() . 'adminmenu/templates/partners/';
    }

    public function dispatch(): void
    {
        $action = $_GET['action'] ?? 'list';
        $smarty = Shop::Smarty();

        match ($action) {
            'create' => $this->showForm($smarty),
            'edit' => $this->showForm($smarty, (int) ($_GET['id'] ?? 0)),
            'save' => $this->save(),
            'delete' => $this->delete(),
            default => $this->showList($smarty),
        };
    }

    private function showList(\Smarty $smarty): void
    {
        $partners = $this->db->getObjects(
            'SELECT p.*, pt.name
             FROM bbf_partners p
             LEFT JOIN bbf_partners_translation pt ON p.id = pt.partner_id AND pt.language_iso = :lang
             ORDER BY p.sort_order, p.id',
            ['lang' => EventConfig::DEFAULT_LANGUAGE]
        );

        $smarty->assign('partners', $partners);
        $smarty->display($this->templatePath . 'list.tpl');
    }

    private function showForm(\Smarty $smarty, int $id = 0): void
    {
        $partner = null;
        $translations = [];

        if ($id > 0) {
            $partner = $this->db->getSingleObject(
                'SELECT * FROM bbf_partners WHERE id = :id',
                ['id' => $id]
            );
            if ($partner === null) {
                header('Location: ?action=list&error=notfound');
                return;
            }
            $translations = $this->db->getObjects(
                'SELECT * FROM bbf_partners_translation WHERE partner_id = :pid',
                ['pid' => $id]
            );
        }

        $categories = $this->db->getObjects(
            'SELECT pc.*, pct.name
             FROM bbf_partner_categories pc
             LEFT JOIN bbf_partner_categories_translation pct ON pc.id = pct.category_id AND pct.language_iso = :lang
             ORDER BY pc.sort_order',
            ['lang' => EventConfig::DEFAULT_LANGUAGE]
        );

        $assignedCatIds = [];
        if ($id > 0) {
            $assigned = $this->db->getObjects(
                'SELECT category_id FROM bbf_partner_category_mapping WHERE partner_id = :pid',
                ['pid' => $id]
            );
            $assignedCatIds = array_map(fn($r) => (int) $r->category_id, $assigned);
        }

        $languages = $this->db->getObjects(
            "SELECT cISO as iso, cNameDeutsch as name FROM tsprache WHERE active = 1 ORDER BY cISO"
        );

        $smarty->assign('partner', $partner);
        $smarty->assign('translations', $translations);
        $smarty->assign('categories', $categories);
        $smarty->assign('assignedCatIds', $assignedCatIds);
        $smarty->assign('languages', $languages ?: [(object) ['iso' => 'ger', 'name' => 'Deutsch']]);
        $smarty->assign('isEdit', $id > 0);
        $smarty->display($this->templatePath . 'edit.tpl');
    }

    private function save(): void
    {
        $id = (int) ($_POST['partner_id'] ?? 0);
        $isNew = $id === 0;

        $firstName = $_POST['trans_ger_name'] ?? '';
        $slug = ($_POST['slug'] ?? '') !== '' ? $_POST['slug'] : SlugHelper::generate($firstName);
        $slug = SlugHelper::ensureUnique($slug, function (string $s) use ($id, $isNew) {
            $exclude = $isNew ? '' : ' AND id != :eid';
            $params = ['slug' => $s];
            if (!$isNew) {
                $params['eid'] = $id;
            }
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

        // Save translations
        $languages = $this->db->getObjects("SELECT cISO as iso FROM tsprache WHERE active = 1");
        foreach ($languages ?: [(object) ['iso' => 'ger']] as $lang) {
            $iso = $lang->iso;
            $name = $_POST['trans_' . $iso . '_name'] ?? '';
            if ($name === '') {
                continue;
            }

            $tData = (object) [
                'partner_id' => $id,
                'language_iso' => $iso,
                'name' => trim($name),
                'short_desc' => ($_POST['trans_' . $iso . '_short_desc'] ?? '') !== '' ? $_POST['trans_' . $iso . '_short_desc'] : null,
                'long_desc' => ($_POST['trans_' . $iso . '_long_desc'] ?? '') !== '' ? $_POST['trans_' . $iso . '_long_desc'] : null,
                'cta_label' => ($_POST['trans_' . $iso . '_cta_label'] ?? '') !== '' ? $_POST['trans_' . $iso . '_cta_label'] : null,
                'cta_url' => ($_POST['trans_' . $iso . '_cta_url'] ?? '') !== '' ? $_POST['trans_' . $iso . '_cta_url'] : null,
            ];

            $existing = $this->db->getSingleObject(
                'SELECT id FROM bbf_partners_translation WHERE partner_id = :pid AND language_iso = :lang',
                ['pid' => $id, 'lang' => $iso]
            );

            if ($existing) {
                $this->db->update('bbf_partners_translation', 'id', (int) $existing->id, $tData);
            } else {
                $this->db->insert('bbf_partners_translation', $tData);
            }
        }

        // Sync categories
        $this->db->executeQuery('DELETE FROM bbf_partner_category_mapping WHERE partner_id = :pid', ['pid' => $id]);
        foreach ($_POST['categories'] ?? [] as $catId) {
            $this->db->insert('bbf_partner_category_mapping', (object) [
                'partner_id' => $id,
                'category_id' => (int) $catId,
            ]);
        }

        header('Location: ?action=edit&id=' . $id . '&msg=' . ($isNew ? 'created' : 'updated'));
    }

    private function delete(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id > 0) {
            $this->db->delete('bbf_partners', 'id', $id);
        }
        header('Location: ?action=list&msg=deleted');
    }
}
