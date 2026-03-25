<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Controller\Backend;

use JTL\DB\DbInterface;
use JTL\Shop;
use Plugin\bbfdesign_events\src\Config\EventConfig;
use Plugin\bbfdesign_events\src\Helper\SlugHelper;

class AreaAdminController
{
    private DbInterface $db;
    private string $templatePath;

    public function __construct()
    {
        $this->db = Shop::Container()->getDB();
        $this->templatePath = EventConfig::getPluginPath() . 'adminmenu/templates/areas/';
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
            'save_marker' => $this->saveMarker(),
            'delete_marker' => $this->deleteMarker(),
            'save_group' => $this->saveGroup(),
            'delete_group' => $this->deleteGroup(),
            default => $this->showList($smarty),
        };
    }

    private function showList(\Smarty $smarty): void
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

        $smarty->assign('maps', $maps);
        $smarty->display($this->templatePath . 'list.tpl');
    }

    private function showForm(\Smarty $smarty, int $id = 0): void
    {
        $map = null;
        $translations = [];
        $markers = [];
        $groups = [];

        if ($id > 0) {
            $map = $this->db->getSingleObject('SELECT * FROM bbf_area_maps WHERE id = :id', ['id' => $id]);
            if ($map === null) {
                header('Location: ?action=list&error=notfound');
                return;
            }
            $translations = $this->db->getObjects(
                'SELECT * FROM bbf_area_maps_translation WHERE map_id = :mid', ['mid' => $id]
            );
            $groups = $this->db->getObjects(
                'SELECT g.*, gt.name
                 FROM bbf_area_marker_groups g
                 LEFT JOIN bbf_area_marker_groups_translation gt ON g.id = gt.group_id AND gt.language_iso = :lang
                 WHERE g.map_id = :mid ORDER BY g.sort_order',
                ['mid' => $id, 'lang' => EventConfig::DEFAULT_LANGUAGE]
            );
            $markers = $this->db->getObjects(
                'SELECT m.*, mt.title, mt.description as marker_desc,
                        g.color as group_color, gt.name as group_name
                 FROM bbf_area_markers m
                 LEFT JOIN bbf_area_markers_translation mt ON m.id = mt.marker_id AND mt.language_iso = :lang
                 LEFT JOIN bbf_area_marker_groups g ON m.group_id = g.id
                 LEFT JOIN bbf_area_marker_groups_translation gt ON g.id = gt.group_id AND gt.language_iso = :lang
                 WHERE m.map_id = :mid ORDER BY m.sort_order',
                ['mid' => $id, 'lang' => EventConfig::DEFAULT_LANGUAGE]
            );
        }

        $languages = $this->db->getObjects(
            "SELECT cISO as iso, cNameDeutsch as name FROM tsprache WHERE active = 1 ORDER BY cISO"
        );

        $smarty->assign('map', $map);
        $smarty->assign('translations', $translations);
        $smarty->assign('markers', $markers);
        $smarty->assign('groups', $groups);
        $smarty->assign('languages', $languages ?: [(object) ['iso' => 'ger', 'name' => 'Deutsch']]);
        $smarty->assign('isEdit', $id > 0);
        $smarty->assign('mapTypes', ['interactive' => 'Interaktive Karte', 'static_image' => 'Statisches Bild', 'list' => 'Nur Liste']);
        $smarty->display($this->templatePath . 'edit.tpl');
    }

    private function save(): void
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

        if ($isNew) {
            $id = $this->db->insert('bbf_area_maps', $data);
        } else {
            $this->db->update('bbf_area_maps', 'id', $id, $data);
        }

        // Translations
        $languages = $this->db->getObjects("SELECT cISO as iso FROM tsprache WHERE active = 1");
        foreach ($languages ?: [(object) ['iso' => 'ger']] as $lang) {
            $iso = $lang->iso;
            $title = $_POST['trans_' . $iso . '_title'] ?? '';
            if ($title === '') { continue; }

            $tData = (object) [
                'map_id' => $id,
                'language_iso' => $iso,
                'title' => trim($title),
                'description' => ($_POST['trans_' . $iso . '_description'] ?? '') !== '' ? $_POST['trans_' . $iso . '_description'] : null,
            ];

            $existing = $this->db->getSingleObject(
                'SELECT id FROM bbf_area_maps_translation WHERE map_id = :mid AND language_iso = :lang',
                ['mid' => $id, 'lang' => $iso]
            );

            if ($existing) {
                $this->db->update('bbf_area_maps_translation', 'id', (int) $existing->id, $tData);
            } else {
                $this->db->insert('bbf_area_maps_translation', $tData);
            }
        }

        header('Location: ?action=edit&id=' . $id . '&msg=' . ($isNew ? 'created' : 'updated'));
    }

    private function delete(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id > 0) { $this->db->delete('bbf_area_maps', 'id', $id); }
        header('Location: ?action=list&msg=deleted');
    }

    private function saveMarker(): void
    {
        $markerId = (int) ($_POST['marker_id'] ?? 0);
        $mapId = (int) ($_POST['map_id'] ?? 0);
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

        if ($isNew) {
            $markerId = $this->db->insert('bbf_area_markers', $data);
        } else {
            $this->db->update('bbf_area_markers', 'id', $markerId, $data);
        }

        // Translation
        $languages = $this->db->getObjects("SELECT cISO as iso FROM tsprache WHERE active = 1");
        foreach ($languages ?: [(object) ['iso' => 'ger']] as $lang) {
            $iso = $lang->iso;
            $title = $_POST['marker_trans_' . $iso . '_title'] ?? '';
            if ($title === '') { continue; }

            $tData = (object) [
                'marker_id' => $markerId,
                'language_iso' => $iso,
                'title' => trim($title),
                'description' => ($_POST['marker_trans_' . $iso . '_description'] ?? '') !== '' ? $_POST['marker_trans_' . $iso . '_description'] : null,
            ];

            $existing = $this->db->getSingleObject(
                'SELECT id FROM bbf_area_markers_translation WHERE marker_id = :mid AND language_iso = :lang',
                ['mid' => $markerId, 'lang' => $iso]
            );
            if ($existing) {
                $this->db->update('bbf_area_markers_translation', 'id', (int) $existing->id, $tData);
            } else {
                $this->db->insert('bbf_area_markers_translation', $tData);
            }
        }

        header('Location: ?action=edit&id=' . $mapId . '&msg=marker_saved#markers');
    }

    private function deleteMarker(): void
    {
        $markerId = (int) ($_GET['marker_id'] ?? 0);
        $mapId = (int) ($_GET['map_id'] ?? 0);
        if ($markerId > 0) { $this->db->delete('bbf_area_markers', 'id', $markerId); }
        header('Location: ?action=edit&id=' . $mapId . '&msg=marker_deleted#markers');
    }

    private function saveGroup(): void
    {
        $groupId = (int) ($_POST['group_id'] ?? 0);
        $mapId = (int) ($_POST['map_id'] ?? 0);
        $isNew = $groupId === 0;

        $data = (object) [
            'map_id' => $mapId,
            'color' => $_POST['group_color'] ?? '#EF4444',
            'icon' => ($_POST['group_icon'] ?? '') !== '' ? $_POST['group_icon'] : null,
            'sort_order' => (int) ($_POST['group_sort_order'] ?? 0),
        ];

        if ($isNew) {
            $groupId = $this->db->insert('bbf_area_marker_groups', $data);
        } else {
            $this->db->update('bbf_area_marker_groups', 'id', $groupId, $data);
        }

        $languages = $this->db->getObjects("SELECT cISO as iso FROM tsprache WHERE active = 1");
        foreach ($languages ?: [(object) ['iso' => 'ger']] as $lang) {
            $iso = $lang->iso;
            $name = $_POST['group_trans_' . $iso . '_name'] ?? '';
            if ($name === '') { continue; }

            $tData = (object) [
                'group_id' => $groupId,
                'language_iso' => $iso,
                'name' => trim($name),
            ];

            $existing = $this->db->getSingleObject(
                'SELECT id FROM bbf_area_marker_groups_translation WHERE group_id = :gid AND language_iso = :lang',
                ['gid' => $groupId, 'lang' => $iso]
            );
            if ($existing) {
                $this->db->update('bbf_area_marker_groups_translation', 'id', (int) $existing->id, $tData);
            } else {
                $this->db->insert('bbf_area_marker_groups_translation', $tData);
            }
        }

        header('Location: ?action=edit&id=' . $mapId . '&msg=group_saved#groups');
    }

    private function deleteGroup(): void
    {
        $groupId = (int) ($_GET['group_id'] ?? 0);
        $mapId = (int) ($_GET['map_id'] ?? 0);
        if ($groupId > 0) { $this->db->delete('bbf_area_marker_groups', 'id', $groupId); }
        header('Location: ?action=edit&id=' . $mapId . '&msg=group_deleted#groups');
    }
}
