<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Controller\Backend;

use JTL\DB\DbInterface;
use JTL\Smarty\JTLSmarty;
use Plugin\bbfdesign_events\src\Config\EventConfig;
use Plugin\bbfdesign_events\src\Helper\SlugHelper;

class AreaAdminController
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
            'save_marker' => $this->handleSaveMarker(),
            'delete_marker' => $this->handleDeleteMarker(),
            'save_group' => $this->handleSaveGroup(),
            'delete_group' => $this->handleDeleteGroup(),
            default => $this->prepareList(),
        };
    }

    private function prepareList(): void
    {
        $maps = $this->db->getObjects(
            'SELECT am.*, amt.title,
                    (SELECT COUNT(*) FROM bbf_area_markers m WHERE m.map_id = am.id) as marker_count,
                    (SELECT COUNT(*) FROM bbf_event_area_mapping eam WHERE eam.map_id = am.id) as event_count
             FROM bbf_area_maps am
             LEFT JOIN bbf_area_maps_translation amt ON am.id = amt.map_id AND amt.language_iso = :lang
             ORDER BY am.id DESC',
            ['lang' => EventConfig::DEFAULT_LANGUAGE]
        );
        $this->smarty->assign('maps', $maps);
    }

    private function prepareForm(int $id): void
    {
        $map = null;
        $translations = [];
        $markers = [];
        $groups = [];

        if ($id > 0) {
            $map = $this->db->getSingleObject('SELECT * FROM bbf_area_maps WHERE id = :id', ['id' => $id]);
            if ($map === null) {
                header('Location: ' . $this->buildUrl('areas') . '&error=notfound');
                exit;
            }
            $translations = $this->db->getObjects('SELECT * FROM bbf_area_maps_translation WHERE map_id = :mid', ['mid' => $id]);
            $groups = $this->db->getObjects(
                'SELECT g.*, gt.name FROM bbf_area_marker_groups g
                 LEFT JOIN bbf_area_marker_groups_translation gt ON g.id = gt.group_id AND gt.language_iso = :lang
                 WHERE g.map_id = :mid ORDER BY g.sort_order',
                ['mid' => $id, 'lang' => EventConfig::DEFAULT_LANGUAGE]
            );
            $markers = $this->db->getObjects(
                'SELECT m.*, mt.title, mt.description as marker_desc, g.color as group_color, gt.name as group_name
                 FROM bbf_area_markers m
                 LEFT JOIN bbf_area_markers_translation mt ON m.id = mt.marker_id AND mt.language_iso = :lang
                 LEFT JOIN bbf_area_marker_groups g ON m.group_id = g.id
                 LEFT JOIN bbf_area_marker_groups_translation gt ON g.id = gt.group_id AND gt.language_iso = :lang
                 WHERE m.map_id = :mid ORDER BY m.sort_order',
                ['mid' => $id, 'lang' => EventConfig::DEFAULT_LANGUAGE]
            );
        }

        $this->smarty->assign('map', $map);
        $this->smarty->assign('translations', $translations);
        $this->smarty->assign('markers', $markers);
        $this->smarty->assign('groups', $groups);
        $this->smarty->assign('languages', $this->getLanguages());
        $this->smarty->assign('isEdit', $id > 0);
        $this->smarty->assign('mapTypes', ['interactive' => 'Interaktive Karte', 'static_image' => 'Statisches Bild', 'list' => 'Nur Liste']);
        $this->smarty->assign('activePage', 'area_edit');
    }

    private function handleSave(): void
    {
        $id = (int) ($_POST['map_id'] ?? 0);
        $isNew = $id === 0;

        $firstName = $_POST['trans_ger_title'] ?? '';
        $slug = ($_POST['slug'] ?? '') !== '' ? $_POST['slug'] : SlugHelper::generate($firstName);
        $slug = SlugHelper::ensureUnique($slug, function (string $s) use ($id, $isNew) {
            $exclude = $isNew ? '' : ' AND id != :eid';
            $params = ['slug' => $s];
            if (!$isNew) { $params['eid'] = $id; }
            $r = $this->db->getSingleObject("SELECT COUNT(*) as cnt FROM bbf_area_maps WHERE slug = :slug{$exclude}", $params);
            return (int) ($r->cnt ?? 0) > 0;
        });

        $data = (object) [
            'slug' => $slug,
            'map_type' => $_POST['map_type'] ?? 'interactive',
            'static_image' => ($_POST['static_image'] ?? '') !== '' ? $_POST['static_image'] : null,
            'center_lat' => ($_POST['center_lat'] ?? '') !== '' ? (float) $_POST['center_lat'] : null,
            'center_lng' => ($_POST['center_lng'] ?? '') !== '' ? (float) $_POST['center_lng'] : null,
            'zoom_level' => (int) ($_POST['zoom_level'] ?? 14),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
        ];

        if ($isNew) { $id = $this->db->insert('bbf_area_maps', $data); }
        else { $this->db->update('bbf_area_maps', 'id', $id, $data); }

        foreach ($this->getLanguages() as $lang) {
            $iso = $lang->iso;
            $title = $_POST['trans_' . $iso . '_title'] ?? '';
            if ($title === '') { continue; }
            $tData = (object) ['map_id' => $id, 'language_iso' => $iso, 'title' => trim($title), 'description' => ($_POST['trans_' . $iso . '_description'] ?? '') !== '' ? $_POST['trans_' . $iso . '_description'] : null];
            $existing = $this->db->getSingleObject('SELECT id FROM bbf_area_maps_translation WHERE map_id = :mid AND language_iso = :lang', ['mid' => $id, 'lang' => $iso]);
            if ($existing) { $this->db->update('bbf_area_maps_translation', 'id', (int) $existing->id, $tData); }
            else { $this->db->insert('bbf_area_maps_translation', $tData); }
        }

        header('Location: ' . $this->buildUrl('areas', 'edit', $id) . '&msg=' . ($isNew ? 'created' : 'updated'));
        exit;
    }

    private function handleDelete(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id > 0) { $this->db->delete('bbf_area_maps', 'id', $id); }
        header('Location: ' . $this->buildUrl('areas') . '&msg=deleted');
        exit;
    }

    private function handleSaveMarker(): void
    {
        $mapId = (int) ($_POST['map_id'] ?? 0);
        $markerId = (int) ($_POST['marker_id'] ?? 0);
        $isNew = $markerId === 0;

        $data = (object) [
            'map_id' => $mapId,
            'group_id' => ($_POST['group_id'] ?? '') !== '' ? (int) $_POST['group_id'] : null,
            'lat' => ($_POST['lat'] ?? '') !== '' ? (float) $_POST['lat'] : null,
            'lng' => ($_POST['lng'] ?? '') !== '' ? (float) $_POST['lng'] : null,
            'pos_x' => ($_POST['pos_x'] ?? '') !== '' ? (float) $_POST['pos_x'] : null,
            'pos_y' => ($_POST['pos_y'] ?? '') !== '' ? (float) $_POST['pos_y'] : null,
            'image' => ($_POST['marker_image'] ?? '') !== '' ? $_POST['marker_image'] : null,
            'sort_order' => (int) ($_POST['marker_sort_order'] ?? 0),
        ];

        if ($isNew) { $markerId = $this->db->insert('bbf_area_markers', $data); }
        else { $this->db->update('bbf_area_markers', 'id', $markerId, $data); }

        foreach ($this->getLanguages() as $lang) {
            $iso = $lang->iso;
            $title = $_POST['marker_trans_' . $iso . '_title'] ?? '';
            if ($title === '') { continue; }
            $tData = (object) ['marker_id' => $markerId, 'language_iso' => $iso, 'title' => trim($title), 'description' => ($_POST['marker_trans_' . $iso . '_description'] ?? '') !== '' ? $_POST['marker_trans_' . $iso . '_description'] : null];
            $existing = $this->db->getSingleObject('SELECT id FROM bbf_area_markers_translation WHERE marker_id = :mid AND language_iso = :lang', ['mid' => $markerId, 'lang' => $iso]);
            if ($existing) { $this->db->update('bbf_area_markers_translation', 'id', (int) $existing->id, $tData); }
            else { $this->db->insert('bbf_area_markers_translation', $tData); }
        }

        header('Location: ' . $this->buildUrl('areas', 'edit', $mapId) . '&msg=marker_saved#markers');
        exit;
    }

    private function handleDeleteMarker(): void
    {
        $markerId = (int) ($_GET['marker_id'] ?? 0);
        $mapId = (int) ($_GET['map_id'] ?? 0);
        if ($markerId > 0) { $this->db->delete('bbf_area_markers', 'id', $markerId); }
        header('Location: ' . $this->buildUrl('areas', 'edit', $mapId) . '&msg=marker_deleted#markers');
        exit;
    }

    private function handleSaveGroup(): void
    {
        $mapId = (int) ($_POST['map_id'] ?? 0);
        $groupId = (int) ($_POST['group_id'] ?? 0);
        $isNew = $groupId === 0;

        $data = (object) [
            'map_id' => $mapId,
            'color' => $_POST['group_color'] ?? '#EF4444',
            'icon' => ($_POST['group_icon'] ?? '') !== '' ? $_POST['group_icon'] : null,
            'sort_order' => (int) ($_POST['group_sort_order'] ?? 0),
        ];

        if ($isNew) { $groupId = $this->db->insert('bbf_area_marker_groups', $data); }
        else { $this->db->update('bbf_area_marker_groups', 'id', $groupId, $data); }

        foreach ($this->getLanguages() as $lang) {
            $iso = $lang->iso;
            $name = $_POST['group_trans_' . $iso . '_name'] ?? '';
            if ($name === '') { continue; }
            $tData = (object) ['group_id' => $groupId, 'language_iso' => $iso, 'name' => trim($name)];
            $existing = $this->db->getSingleObject('SELECT id FROM bbf_area_marker_groups_translation WHERE group_id = :gid AND language_iso = :lang', ['gid' => $groupId, 'lang' => $iso]);
            if ($existing) { $this->db->update('bbf_area_marker_groups_translation', 'id', (int) $existing->id, $tData); }
            else { $this->db->insert('bbf_area_marker_groups_translation', $tData); }
        }

        header('Location: ' . $this->buildUrl('areas', 'edit', $mapId) . '&msg=group_saved#groups');
        exit;
    }

    private function handleDeleteGroup(): void
    {
        $groupId = (int) ($_GET['group_id'] ?? 0);
        $mapId = (int) ($_GET['map_id'] ?? 0);
        if ($groupId > 0) { $this->db->delete('bbf_area_marker_groups', 'id', $groupId); }
        header('Location: ' . $this->buildUrl('areas', 'edit', $mapId) . '&msg=group_deleted#groups');
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
