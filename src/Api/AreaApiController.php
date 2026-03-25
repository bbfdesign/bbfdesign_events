<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Api;

use JTL\Shop;
use Plugin\bbfdesign_events\src\Service\AreaService;

class AreaApiController
{
    private AreaService $areaService;

    public function __construct()
    {
        $this->areaService = new AreaService(Shop::Container()->getDB());
    }

    public function getByEvent(int $eventId, string $lang): array
    {
        $maps = $this->areaService->getMapsForEvent($eventId, $lang);
        return array_map(fn($m) => [
            'id' => $m->id,
            'slug' => $m->slug,
            'title' => $m->getTitle(),
            'description' => $m->getDescription(),
            'map_type' => $m->mapType,
            'center_lat' => $m->centerLat,
            'center_lng' => $m->centerLng,
            'zoom_level' => $m->zoomLevel,
            'marker_groups' => array_map(fn($g) => [
                'id' => $g->id,
                'name' => $g->getName(),
                'color' => $g->color,
                'icon' => $g->icon,
            ], $m->markerGroups),
            'markers' => array_map(fn($mk) => [
                'id' => $mk->id,
                'title' => $mk->getTitle(),
                'description' => $mk->getDescription(),
                'lat' => $mk->lat,
                'lng' => $mk->lng,
                'group_id' => $mk->groupId,
                'image' => $mk->image,
            ], $m->markers),
        ], $maps);
    }
}
