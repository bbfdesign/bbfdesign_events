<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Api;

use JTL\Shop;
use Plugin\bbfdesign_events\src\Service\KnowledgeService;

class KnowledgeApiController
{
    private KnowledgeService $knowledgeService;

    public function __construct()
    {
        $this->knowledgeService = new KnowledgeService(Shop::Container()->getDB());
    }

    public function getAll(string $lang): array
    {
        $items = $this->knowledgeService->getAllItems($lang);
        return array_map(fn($i) => [
            'id' => $i->id,
            'slug' => $i->slug,
            'title' => $i->getTitle(),
            'teaser' => $i->getTeaser(),
            'image' => $i->image,
            'icon' => $i->icon,
        ], $items);
    }

    public function getByEvent(int $eventId, string $lang): array
    {
        $items = $this->knowledgeService->getItemsForEvent($eventId, $lang);
        return array_map(fn($i) => [
            'id' => $i->id,
            'title' => $i->getTitle(),
            'teaser' => $i->getTeaser(),
            'content' => $i->getContent(),
            'image' => $i->image,
        ], $items);
    }
}
