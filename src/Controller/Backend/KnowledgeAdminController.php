<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Controller\Backend;

use JTL\DB\DbInterface;
use JTL\Shop;
use Plugin\bbfdesign_events\src\Config\EventConfig;
use Plugin\bbfdesign_events\src\Helper\SlugHelper;

class KnowledgeAdminController
{
    private DbInterface $db;
    private string $templatePath;

    public function __construct()
    {
        $this->db = Shop::Container()->getDB();
        $this->templatePath = EventConfig::getPluginPath() . 'adminmenu/templates/knowledge/';
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
        $items = $this->db->getObjects(
            'SELECT ki.*, kit.title
             FROM bbf_knowledge_items ki
             LEFT JOIN bbf_knowledge_items_translation kit ON ki.id = kit.item_id AND kit.language_iso = :lang
             ORDER BY ki.sort_order, ki.id',
            ['lang' => EventConfig::DEFAULT_LANGUAGE]
        );

        $smarty->assign('items', $items);
        $smarty->display($this->templatePath . 'list.tpl');
    }

    private function showForm(\Smarty $smarty, int $id = 0): void
    {
        $item = null;
        $translations = [];

        if ($id > 0) {
            $item = $this->db->getSingleObject('SELECT * FROM bbf_knowledge_items WHERE id = :id', ['id' => $id]);
            if ($item === null) {
                header('Location: ?action=list&error=notfound');
                return;
            }
            $translations = $this->db->getObjects(
                'SELECT * FROM bbf_knowledge_items_translation WHERE item_id = :iid',
                ['iid' => $id]
            );
        }

        $languages = $this->db->getObjects(
            "SELECT cISO as iso, cNameDeutsch as name FROM tsprache WHERE active = 1 ORDER BY cISO"
        );

        $smarty->assign('item', $item);
        $smarty->assign('translations', $translations);
        $smarty->assign('languages', $languages ?: [(object) ['iso' => 'ger', 'name' => 'Deutsch']]);
        $smarty->assign('isEdit', $id > 0);
        $smarty->display($this->templatePath . 'edit.tpl');
    }

    private function save(): void
    {
        $id = (int) ($_POST['item_id'] ?? 0);
        $isNew = $id === 0;

        $firstName = $_POST['trans_ger_title'] ?? '';
        $slug = ($_POST['slug'] ?? '') !== '' ? $_POST['slug'] : SlugHelper::generate($firstName);
        $slug = SlugHelper::ensureUnique($slug, function (string $s) use ($id, $isNew) {
            $exclude = $isNew ? '' : ' AND id != :eid';
            $params = ['slug' => $s];
            if (!$isNew) {
                $params['eid'] = $id;
            }
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

        // Translations
        $languages = $this->db->getObjects("SELECT cISO as iso FROM tsprache WHERE active = 1");
        foreach ($languages ?: [(object) ['iso' => 'ger']] as $lang) {
            $iso = $lang->iso;
            $title = $_POST['trans_' . $iso . '_title'] ?? '';
            if ($title === '') {
                continue;
            }

            $tData = (object) [
                'item_id' => $id,
                'language_iso' => $iso,
                'title' => trim($title),
                'teaser' => ($_POST['trans_' . $iso . '_teaser'] ?? '') !== '' ? $_POST['trans_' . $iso . '_teaser'] : null,
                'content' => ($_POST['trans_' . $iso . '_content'] ?? '') !== '' ? $_POST['trans_' . $iso . '_content'] : null,
                'cta_label' => ($_POST['trans_' . $iso . '_cta_label'] ?? '') !== '' ? $_POST['trans_' . $iso . '_cta_label'] : null,
                'cta_url' => ($_POST['trans_' . $iso . '_cta_url'] ?? '') !== '' ? $_POST['trans_' . $iso . '_cta_url'] : null,
            ];

            $existing = $this->db->getSingleObject(
                'SELECT id FROM bbf_knowledge_items_translation WHERE item_id = :iid AND language_iso = :lang',
                ['iid' => $id, 'lang' => $iso]
            );

            if ($existing) {
                $this->db->update('bbf_knowledge_items_translation', 'id', (int) $existing->id, $tData);
            } else {
                $this->db->insert('bbf_knowledge_items_translation', $tData);
            }
        }

        header('Location: ?action=edit&id=' . $id . '&msg=' . ($isNew ? 'created' : 'updated'));
    }

    private function delete(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id > 0) {
            $this->db->delete('bbf_knowledge_items', 'id', $id);
        }
        header('Location: ?action=list&msg=deleted');
    }
}
