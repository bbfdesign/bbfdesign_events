<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Api;

use Plugin\bbfdesign_events\src\Service\EventService;
use Plugin\bbfdesign_events\src\Service\KnowledgeService;
use Plugin\bbfdesign_events\src\Service\PartnerService;

class EventApiController
{
    public function __construct(
        private readonly EventService $eventService,
        private readonly PartnerService $partnerService,
        private readonly KnowledgeService $knowledgeService
    ) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getPublishedEvents(string $lang, int $limit = 50, int $offset = 0): array
    {
        $events = $this->eventService->getAllPublished($lang);
        return array_slice(
            array_map(fn($e) => $this->serializeEvent($e), $events),
            $offset,
            $limit
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getUpcomingEvents(string $lang, int $limit = 10): array
    {
        $events = $this->eventService->getUpcomingEvents($lang, $limit);
        return array_map(fn($e) => $this->serializeEvent($e), $events);
    }

    public function getEventBySlug(string $slug, string $lang): ?array
    {
        $event = $this->eventService->getEventBySlug($slug, $lang);
        return $event !== null ? $this->serializeEvent($event) : null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getPartners(string $lang): array
    {
        $partners = $this->partnerService->getAllPartners($lang);
        return array_map(fn($p) => [
            'id' => $p->id,
            'slug' => $p->slug,
            'name' => $p->getName(),
            'logo' => $p->logo,
            'website_url' => $p->websiteUrl,
            'short_desc' => $p->getShortDesc(),
        ], $partners);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getPartnersByEvent(int $eventId, string $lang): array
    {
        $partners = $this->partnerService->getPartnersForEvent($eventId, $lang);
        return array_map(fn($p) => [
            'id' => $p->id,
            'name' => $p->getName(),
            'logo' => $p->logo,
            'website_url' => $p->websiteUrl,
        ], $partners);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getKnowledgeItems(string $lang): array
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

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getKnowledgeByEvent(int $eventId, string $lang): array
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

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getSearchableContent(string $lang): array
    {
        $events = $this->eventService->getAllPublished($lang);
        $results = [];

        foreach ($events as $event) {
            $results[] = [
                'type' => 'event',
                'id' => $event->id,
                'title' => $event->getTitle(),
                'description' => $event->getTeaser(),
                'content' => strip_tags($event->getDescription()),
                'url' => $event->url,
                'image' => $event->heroImage,
                'date' => $event->nextDate?->format('Y-m-d'),
                'categories' => array_map(fn($c) => $c->getName(), $event->categories),
            ];
        }

        return $results;
    }

    private function serializeEvent(\Plugin\bbfdesign_events\src\Model\Event $event): array
    {
        return [
            'id' => $event->id,
            'slug' => $event->slug,
            'title' => $event->getTitle(),
            'subtitle' => $event->getSubtitle(),
            'teaser' => $event->getTeaser(),
            'hero_image' => $event->heroImage,
            'url' => $event->url,
            'status' => $event->status->value,
            'computed_status' => $event->computedStatus,
            'is_featured' => $event->isFeatured,
            'next_date' => $event->nextDate?->format('Y-m-d'),
            'categories' => array_map(fn($c) => [
                'id' => $c->id,
                'slug' => $c->slug,
                'name' => $c->getName(),
            ], $event->categories),
            'dates' => array_map(fn($d) => [
                'date_start' => $d->dateStart->format('Y-m-d'),
                'date_end' => $d->dateEnd?->format('Y-m-d'),
                'is_allday' => $d->isAllday,
            ], $event->dates),
        ];
    }
}
