<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Controller\Backend;

use JTL\DB\DbInterface;
use JTL\Smarty\JTLSmarty;
use Plugin\bbfdesign_events\src\Config\EventConfig;
use Plugin\bbfdesign_events\src\Helper\SlugHelper;

class TicketAdminController
{
    public function __construct(
        private readonly DbInterface $db,
        private readonly JTLSmarty $smarty,
        private readonly string $postURL
    ) {}

    public function dispatch(string $action): void
    {
        match ($action) {
            'create_category' => $this->prepareCategoryForm(0),
            'edit_category' => $this->prepareCategoryForm((int) ($_GET['id'] ?? 0)),
            'save_category' => $this->handleSaveCategory(),
            'delete_category' => $this->handleDeleteCategory(),
            default => $this->prepareList(),
        };
    }

    private function prepareList(): void
    {
        $categories = $this->db->getObjects(
            'SELECT tc.*, tct.name, tct.description as cat_desc,
                    (SELECT COUNT(*) FROM bbf_event_tickets t WHERE t.category_id = tc.id) as ticket_count
             FROM bbf_ticket_categories tc
             LEFT JOIN bbf_ticket_categories_translation tct ON tc.id = tct.category_id AND tct.language_iso = :lang
             ORDER BY tc.sort_order, tc.id',
            ['lang' => EventConfig::DEFAULT_LANGUAGE]
        );

        $tickets = $this->db->getObjects(
            "SELECT t.*, tt.name as ticket_name, e.slug as event_slug,
                    et.title as event_title, tct.name as cat_name
             FROM bbf_event_tickets t
             LEFT JOIN bbf_event_tickets_translation tt ON t.id = tt.ticket_id AND tt.language_iso = :lang
             LEFT JOIN bbf_events e ON t.event_id = e.id
             LEFT JOIN bbf_events_translation et ON e.id = et.event_id AND et.language_iso = :lang
             LEFT JOIN bbf_ticket_categories tc ON t.category_id = tc.id
             LEFT JOIN bbf_ticket_categories_translation tct ON tc.id = tct.category_id AND tct.language_iso = :lang
             ORDER BY t.event_id, t.sort_order LIMIT 100",
            ['lang' => EventConfig::DEFAULT_LANGUAGE]
        );

        $this->smarty->assign('categories', $categories);
        $this->smarty->assign('tickets', $tickets);
    }

    private function prepareCategoryForm(int $id): void
    {
        $category = null;
        $translations = [];

        if ($id > 0) {
            $category = $this->db->getSingleObject('SELECT * FROM bbf_ticket_categories WHERE id = :id', ['id' => $id]);
            if ($category === null) {
                header('Location: ' . $this->buildUrl('tickets') . '&error=notfound');
                exit;
            }
            $translations = $this->db->getObjects('SELECT * FROM bbf_ticket_categories_translation WHERE category_id = :cid', ['cid' => $id]);
        }

        $this->smarty->assign('category', $category);
        $this->smarty->assign('translations', $translations);
        $this->smarty->assign('languages', $this->getLanguages());
        $this->smarty->assign('isEdit', $id > 0);
        $this->smarty->assign('activePage', 'ticket_edit');
    }

    private function handleSaveCategory(): void
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

        foreach ($this->getLanguages() as $lang) {
            $iso = $lang->iso;
            $name = $_POST['trans_' . $iso . '_name'] ?? '';
            if ($name === '') { continue; }
            $tData = (object) [
                'category_id' => $id, 'language_iso' => $iso,
                'name' => trim($name),
                'description' => ($_POST['trans_' . $iso . '_description'] ?? '') !== '' ? $_POST['trans_' . $iso . '_description'] : null,
            ];
            $existing = $this->db->getSingleObject('SELECT id FROM bbf_ticket_categories_translation WHERE category_id = :cid AND language_iso = :lang', ['cid' => $id, 'lang' => $iso]);
            if ($existing) { $this->db->update('bbf_ticket_categories_translation', 'id', (int) $existing->id, $tData); }
            else { $this->db->insert('bbf_ticket_categories_translation', $tData); }
        }

        header('Location: ' . $this->buildUrl('tickets', 'edit_category', $id) . '&msg=' . ($isNew ? 'created' : 'updated'));
        exit;
    }

    private function handleDeleteCategory(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id > 0) { $this->db->delete('bbf_ticket_categories', 'id', $id); }
        header('Location: ' . $this->buildUrl('tickets') . '&msg=deleted');
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
