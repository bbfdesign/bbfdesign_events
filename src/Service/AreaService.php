<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Service;

use JTL\DB\DbInterface;
use Plugin\bbfdesign_events\src\Model\Area\AreaMap;
use Plugin\bbfdesign_events\src\Model\Area\AreaMapTranslation;
use Plugin\bbfdesign_events\src\Model\Area\AreaMarker;
use Plugin\bbfdesign_events\src\Model\Area\AreaMarkerGroup;
use Plugin\bbfdesign_events\src\Model\Area\AreaMarkerGroupTranslation;
use Plugin\bbfdesign_events\src\Model\Area\AreaMarkerTranslation;

class AreaService
{
    public function __construct(
        private readonly DbInterface $db
    ) {}

    /**
     * @return AreaMap[]
     */
    public function getMapsForEvent(int $eventId, string $languageIso): array
    {
        $rows = $this->db->getObjects(
            'SELECT am.*, amt.title, amt.description as map_desc, eam.sort_order as event_sort
             FROM bbf_event_area_mapping eam
             JOIN bbf_area_maps am ON eam.map_id = am.id
             LEFT JOIN bbf_area_maps_translation amt ON am.id = amt.map_id AND amt.language_iso = :lang
             WHERE eam.event_id = :eid AND am.is_active = 1
             ORDER BY eam.sort_order',
            ['eid' => $eventId, 'lang' => $languageIso]
        );

        $maps = [];
        foreach ($rows as $row) {
            $map = new AreaMap();
            $map->id = (int) $row->id;
            $map->slug = $row->slug;
            $map->mapType = $row->map_type;
            $map->staticImage = $row->static_image;
            $map->centerLat = $row->center_lat !== null ? (float) $row->center_lat : null;
            $map->centerLng = $row->center_lng !== null ? (float) $row->center_lng : null;
            $map->zoomLevel = (int) ($row->zoom_level ?? 14);
            $map->isActive = (bool) $row->is_active;

            if ($row->title !== null) {
                $t = new AreaMapTranslation();
                $t->mapId = $map->id;
                $t->languageIso = $languageIso;
                $t->title = $row->title;
                $t->description = $row->map_desc;
                $map->translation = $t;
            }

            // Load marker groups + markers
            $this->loadMarkerGroups($map, $languageIso);
            $this->loadMarkers($map, $languageIso);

            $maps[] = $map;
        }

        return $maps;
    }

    private function loadMarkerGroups(AreaMap $map, string $languageIso): void
    {
        $rows = $this->db->getObjects(
            'SELECT g.*, gt.name
             FROM bbf_area_marker_groups g
             LEFT JOIN bbf_area_marker_groups_translation gt ON g.id = gt.group_id AND gt.language_iso = :lang
             WHERE g.map_id = :mid
             ORDER BY g.sort_order',
            ['mid' => $map->id, 'lang' => $languageIso]
        );

        foreach ($rows as $row) {
            $group = new AreaMarkerGroup();
            $group->id = (int) $row->id;
            $group->mapId = (int) $row->map_id;
            $group->color = $row->color ?? '#EF4444';
            $group->icon = $row->icon;
            $group->sortOrder = (int) $row->sort_order;

            if ($row->name !== null) {
                $t = new AreaMarkerGroupTranslation();
                $t->groupId = $group->id;
                $t->languageIso = $languageIso;
                $t->name = $row->name;
                $group->translation = $t;
            }

            $map->markerGroups[] = $group;
        }
    }

    private function loadMarkers(AreaMap $map, string $languageIso): void
    {
        $rows = $this->db->getObjects(
            'SELECT m.*, mt.title, mt.description as marker_desc
             FROM bbf_area_markers m
             LEFT JOIN bbf_area_markers_translation mt ON m.id = mt.marker_id AND mt.language_iso = :lang
             WHERE m.map_id = :mid
             ORDER BY m.sort_order',
            ['mid' => $map->id, 'lang' => $languageIso]
        );

        foreach ($rows as $row) {
            $marker = new AreaMarker();
            $marker->id = (int) $row->id;
            $marker->mapId = (int) $row->map_id;
            $marker->groupId = $row->group_id !== null ? (int) $row->group_id : null;
            $marker->lat = $row->lat !== null ? (float) $row->lat : null;
            $marker->lng = $row->lng !== null ? (float) $row->lng : null;
            $marker->posX = $row->pos_x !== null ? (float) $row->pos_x : null;
            $marker->posY = $row->pos_y !== null ? (float) $row->pos_y : null;
            $marker->image = $row->image;
            $marker->sortOrder = (int) $row->sort_order;

            if ($row->title !== null) {
                $t = new AreaMarkerTranslation();
                $t->markerId = $marker->id;
                $t->languageIso = $languageIso;
                $t->title = $row->title;
                $t->description = $row->marker_desc;
                $marker->translation = $t;
            }

            $map->markers[] = $marker;
        }
    }
}
