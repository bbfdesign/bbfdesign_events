<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Controller\Backend;

use JTL\DB\DbInterface;
use JTL\Smarty\JTLSmarty;
use Plugin\bbfdesign_events\src\Config\EventConfig;
use Plugin\bbfdesign_events\src\Helper\SlugHelper;

class KnowledgeAdminController
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
        $items = $this->db->getObjects(
            'SELECT ki.*, kit.title FROM bbf_knowledge_items ki
             LEFT JOIN bbf_knowledge_items_translation kit ON ki.id = kit.item_id AND kit.language_iso = :lang
             ORDER BY ki.sort_order, ki.id',
            ['lang' => EventConfig::DEFAULT_LANGUAGE]
        );
        $this->smarty->assign('items', $items);
    }

    private function prepareForm(int $id): void
    {
        $item = null;
        $translations = [];

        if ($id > 0) {
            $item = $this->db->getSingleObject('SELECT * FROM bbf_knowledge_items WHERE id = :id', ['id' => $id]);
            if ($item === null) {
                header('Location: ' . $this->buildUrl('knowledge') . '&error=notfound');
                exit;
            }
            $translations = $this->db->getObjects('SELECT * FROM bbf_knowledge_items_translation WHERE item_id = :iid', ['iid' => $id]);
        }

        $this->smarty->assign('item', $item);
        $this->smarty->assign('translations', $translations);
        $this->smarty->assign('languages', $this->getLanguages());
        $this->smarty->assign('isEdit', $id > 0);
        $this->smarty->assign('activePage', 'knowledge_edit');
    }

    private function handleSave(): void
    {
        $id = (int) ($_POST['item_id'] ?? 0);
        $isNew = $id === 0;

        $firstName = $_POST['trans_ger_title'] ?? '';
        $slug = ($_POST['slug'] ?? '') !== '' ? $_POST['slug'] : SlugHelper::generate($firstName);
        $slug = SlugHelper::ensureUnique($slug, function (string $s) use ($id, $isNew) {
            $exclude = $isNew ? '' : ' AND id != :eid';
            $params = ['slug' => $s];
            if (!$isNew) { $params['eid'] = $id; }
            $r = $this->db->getSingleObject("SELECT COUNT(*) as cnt FROM bbf_knowledge_items WHERE slug = :slug{$exclude}", $params);
            return (int) ($r->cnt ?? 0) > 0;
        });

        $data = (object) [
            'slug' => $slug,
            'image' => ($_POST['image'] ?? '') !== '' ? $_POST['image'] : null,
            'icon' => ($_POST['icon'] ?? '') !== '' ? $_POST['icon'] : null,
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'sort_order' => (int) ($_POST['sort_order'] ?? 0),
        ];

        if ($isNew) {
            $id = $this->db->insert('bbf_knowledge_items', $data);
        } else {
            $this->db->update('bbf_knowledge_items', 'id', $id, $data);
        }

        foreach ($this->getLanguages() as $lang) {
            $iso = $lang->iso;
            $title = $_POST['trans_' . $iso . '_title'] ?? '';
            if ($title === '') { continue; }

            $tData = (object) [
                'item_id' => $id, 'language_iso' => $iso,
                'title' => trim($title),
                'teaser' => ($_POST['trans_' . $iso . '_teaser'] ?? '') !== '' ? $_POST['trans_' . $iso . '_teaser'] : null,
                'content' => ($_POST['trans_' . $iso . '_content'] ?? '') !== '' ? $_POST['trans_' . $iso . '_content'] : null,
                'cta_label' => ($_POST['trans_' . $iso . '_cta_label'] ?? '') !== '' ? $_POST['trans_' . $iso . '_cta_label'] : null,
                'cta_url' => ($_POST['trans_' . $iso . '_cta_url'] ?? '') !== '' ? $_POST['trans_' . $iso . '_cta_url'] : null,
            ];
            $existing = $this->db->getSingleObject('SELECT id FROM bbf_knowledge_items_translation WHERE item_id = :iid AND language_iso = :lang', ['iid' => $id, 'lang' => $iso]);
            if ($existing) {
                $this->db->update('bbf_knowledge_items_translation', 'id', (int) $existing->id, $tData);
            } else {
                $this->db->insert('bbf_knowledge_items_translation', $tData);
            }
        }

        header('Location: ' . $this->buildUrl('knowledge', 'edit', $id) . '&msg=' . ($isNew ? 'created' : 'updated'));
        exit;
    }

    private function handleDelete(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id > 0) { $this->db->delete('bbf_knowledge_items', 'id', $id); }
        header('Location: ' . $this->buildUrl('knowledge') . '&msg=deleted');
        exit;
    }

    private function getLanguages(): array
    {
        $rows = $this->db->getObjects("SELECT cISO as iso, cNameDeutsch as name FROM tsprache WHERE active = 1 ORDER BY cISO");
        return $rows ?: [(object) ['iso' => 'ger', 'name' => 'Deutsch']];
    }

    private function buildUrl(string $page, string $action = 'list', ?int $id = null): string
    {
        $separator = (strpos($this->postURL, '?') !== false) ? '&' : '?';
        $url = $this->postURL . $separator . 'bbf_page=' . $page;
        if ($action !== 'list') { $url .= '&action=' . $action; }
        if ($id !== null) { $url .= '&id=' . $id; }
        return $url;
    }
}
