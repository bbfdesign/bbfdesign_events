<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Controller\Backend;

use JTL\DB\DbInterface;
use JTL\Shop;
use Plugin\bbfdesign_events\src\Config\EventConfig;
use Plugin\bbfdesign_events\src\Helper\SlugHelper;

class TicketAdminController
{
    private DbInterface $db;
    private string $templatePath;

    public function __construct()
    {
        $this->db = Shop::Container()->getDB();
        $this->templatePath = EventConfig::getPluginPath() . 'adminmenu/templates/tickets/';
    }

    public function dispatch(): void
    {
        $action = $_GET['action'] ?? 'list';
        $smarty = Shop::Smarty();

        match ($action) {
            'create_category' => $this->showCategoryForm($smarty),
            'edit_category' => $this->showCategoryForm($smarty, (int) ($_GET['id'] ?? 0)),
            'save_category' => $this->saveCategory(),
            'delete_category' => $this->deleteCategory(),
            default => $this->showList($smarty),
        };
    }

    private function showList(\Smarty $smarty): void
    {
        $categories = $this->db->getObjects(
            'SELECT tc.*, tct.name, tct.description as cat_desc,
                    (SELECT COUNT(*) FROM bbf_event_tickets t WHERE t.category_id = tc.id) as ticket_count
             FROM bbf_ticket_categories tc
             LEFT JOIN bbf_ticket_categories_translation tct ON tc.id = tct.category_id AND tct.language_iso = :lang
             ORDER BY tc.sort_order, tc.id',
            ['lang' => EventConfig::DEFAULT_LANGUAGE]
        );

        // Event tickets overview
        $tickets = $this->db->getObjects(
            "SELECT t.*, tt.name as ticket_name, e.slug as event_slug,
                    et.title as event_title, tc.slug as cat_slug, tct.name as cat_name
             FROM bbf_event_tickets t
             LEFT JOIN bbf_event_tickets_translation tt ON t.id = tt.ticket_id AND tt.language_iso = :lang
             LEFT JOIN bbf_events e ON t.event_id = e.id
             LEFT JOIN bbf_events_translation et ON e.id = et.event_id AND et.language_iso = :lang
             LEFT JOIN bbf_ticket_categories tc ON t.category_id = tc.id
             LEFT JOIN bbf_ticket_categories_translation tct ON tc.id = tct.category_id AND tct.language_iso = :lang
             ORDER BY t.event_id, t.sort_order
             LIMIT 100",
            ['lang' => EventConfig::DEFAULT_LANGUAGE]
        );

        $smarty->assign('categories', $categories);
        $smarty->assign('tickets', $tickets);
        $smarty->display($this->templatePath . 'list.tpl');
    }

    private function showCategoryForm(\Smarty $smarty, int $id = 0): void
    {
        $category = null;
        $translations = [];

        if ($id > 0) {
            $category = $this->db->getSingleObject('SELECT * FROM bbf_ticket_categories WHERE id = :id', ['id' => $id]);
            if ($category === null) {
                header('Location: ?action=list&error=notfound');
                return;
            }
            $translations = $this->db->getObjects(
                'SELECT * FROM bbf_ticket_categories_translation WHERE category_id = :cid', ['cid' => $id]
            );
        }

        $languages = $this->db->getObjects(
            "SELECT cISO as iso, cNameDeutsch as name FROM tsprache WHERE active = 1 ORDER BY cISO"
        );

        $smarty->assign('category', $category);
        $smarty->assign('translations', $translations);
        $smarty->assign('languages', $languages ?: [(object) ['iso' => 'ger', 'name' => 'Deutsch']]);
        $smarty->assign('isEdit', $id > 0);
        $smarty->display($this->templatePath . 'edit.tpl');
    }

    private function saveCategory(): void
    {
        $id = (int) ($_POST['category_id'] ?? 0);
        $isNew = $id === 0;

        $firstName = $_POST['trans_ger_name'] ?? '';
        $slug = ($_POST['slug'] ?? '') !== '' ? $_POST['slug'] : SlugHelper::generate($firstName);
        $slug = SlugHelper::ensureUnique($slug, function (string $s) use ($id, $isNew) {
            $exclude = $isNew ? '' : ' AND id != :eid';
            $params = ['slug' => $s];
            if (!$isNew) { $params['eid'] = $id; }
            $r = $this->db->getSingleObject("SELECT COUNT(*) as cnt FROM bbf_ticket_categories WHERE slug = :slug{$exclude}", $params);
            return (int) ($r->cnt ?? 0) > 0;
        });

        $data = (object) [
            'slug' => $slug,
            'color' => $_POST['color'] ?? '#3B82F6',
            'icon' => ($_POST['icon'] ?? '') !== '' ? $_POST['icon'] : null,
            'sort_order' => (int) ($_POST['sort_order'] ?? 0),
        ];

        if ($isNew) {
            $id = $this->db->insert('bbf_ticket_categories', $data);
        } else {
            $this->db->update('bbf_ticket_categories', 'id', $id, $data);
        }

        $languages = $this->db->getObjects("SELECT cISO as iso FROM tsprache WHERE active = 1");
        foreach ($languages ?: [(object) ['iso' => 'ger']] as $lang) {
            $iso = $lang->iso;
            $name = $_POST['trans_' . $iso . '_name'] ?? '';
            if ($name === '') { continue; }

            $tData = (object) [
                'category_id' => $id,
                'language_iso' => $iso,
                'name' => trim($name),
                'description' => ($_POST['trans_' . $iso . '_description'] ?? '') !== '' ? $_POST['trans_' . $iso . '_description'] : null,
            ];

            $existing = $this->db->getSingleObject(
                'SELECT id FROM bbf_ticket_categories_translation WHERE category_id = :cid AND language_iso = :lang',
                ['cid' => $id, 'lang' => $iso]
            );
            if ($existing) {
                $this->db->update('bbf_ticket_categories_translation', 'id', (int) $existing->id, $tData);
            } else {
                $this->db->insert('bbf_ticket_categories_translation', $tData);
            }
        }

        header('Location: ?action=edit_category&id=' . $id . '&msg=' . ($isNew ? 'created' : 'updated'));
    }

    private function deleteCategory(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id > 0) { $this->db->delete('bbf_ticket_categories', 'id', $id); }
        header('Location: ?action=list&msg=deleted');
    }
}
