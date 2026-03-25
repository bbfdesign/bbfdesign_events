<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Service;

use Plugin\bbfdesign_events\src\Config\EventConfig;
use Plugin\bbfdesign_events\src\Helper\SlugHelper;
use Plugin\bbfdesign_events\src\Model\Event;
use Plugin\bbfdesign_events\src\Model\EventTranslation;
use Plugin\bbfdesign_events\src\Repository\EventListFilter;
use Plugin\bbfdesign_events\src\Repository\EventListResult;
use Plugin\bbfdesign_events\src\Repository\EventRepository;

class EventService
{
    public function __construct(
        private readonly EventRepository $repository,
        private readonly EventDateService $dateService,
        private readonly SeoService $seoService,
        private readonly CacheService $cacheService
    ) {}

    public function getEventBySlug(string $slug, string $languageIso): ?Event
    {
        $event = $this->repository->findBySlug($slug);
        if ($event === null || !$event->isVisible()) {
            return null;
        }
        $this->hydrateEvent($event, $languageIso);
        return $event;
    }

    public function getEventById(int $id, string $languageIso): ?Event
    {
        $event = $this->repository->findById($id);
        if ($event === null) {
            return null;
        }
        $this->hydrateEvent($event, $languageIso);
        return $event;
    }

    public function getEventListing(EventListFilter $filter): EventListResult
    {
        $result = $this->repository->findByFilter($filter);

        foreach ($result->events as $event) {
            $this->hydrateEvent($event, $filter->languageIso);
        }

        return $result;
    }

    public function saveEvent(Event $event): int
    {
        if ($event->slug === '' && !empty($event->translations)) {
            $title = $event->translations[0]->title ?? 'event';
            $event->slug = SlugHelper::generate($title);
        }

        $event->slug = SlugHelper::ensureUnique(
            $event->slug,
            fn(string $slug) => $this->repository->slugExists($slug, $event->id > 0 ? $event->id : null)
        );

        $eventId = $this->repository->save($event);
        $event->id = $eventId;

        foreach ($event->translations as $translation) {
            $translation->eventId = $eventId;
            $this->repository->saveTranslation($translation);
        }

        $this->cacheService->invalidateEvent($eventId);
        $this->cacheService->invalidateListings();

        return $eventId;
    }

    public function deleteEvent(int $id): bool
    {
        $result = $this->repository->delete($id);
        if ($result) {
            $this->cacheService->invalidateEvent($id);
            $this->cacheService->invalidateListings();
        }
        return $result;
    }

    public function syncCategories(int $eventId, array $categoryIds): void
    {
        $this->repository->syncCategories($eventId, $categoryIds);
        $this->cacheService->invalidateEvent($eventId);
    }

    /**
     * @return Event[]
     */
    public function getAllPublished(string $languageIso): array
    {
        $filter = new EventListFilter();
        $filter->languageIso = $languageIso;
        $filter->perPage = 9999;
        $result = $this->getEventListing($filter);
        return $result->events;
    }

    /**
     * @return Event[]
     */
    public function getUpcomingEvents(string $languageIso, int $limit = 10): array
    {
        $filter = new EventListFilter();
        $filter->languageIso = $languageIso;
        $filter->temporalStatus = 'upcoming';
        $filter->sortBy = 'date_asc';
        $filter->perPage = $limit;
        $result = $this->getEventListing($filter);
        return $result->events;
    }

    public function computeTemporalStatus(Event $event): string
    {
        return $this->dateService->computeStatus($event->dates);
    }

    private function hydrateEvent(Event $event, string $languageIso): void
    {
        $event->translation = $this->resolveTranslation($event, $languageIso);
        $event->computedStatus = $this->computeTemporalStatus($event);
        $event->nextDate = $this->dateService->getNextDate($event->dates);
        $event->url = $this->seoService->getEventUrl($event, $languageIso);

        foreach ($event->categories as $cat) {
            $cat->translation = $this->resolveCategoryTranslation($cat, $languageIso);
        }
    }

    private function resolveTranslation(Event $event, string $languageIso): ?EventTranslation
    {
        foreach ($event->translations as $t) {
            if ($t->languageIso === $languageIso) {
                return $t;
            }
        }
        foreach ($event->translations as $t) {
            if ($t->languageIso === EventConfig::DEFAULT_LANGUAGE) {
                return $t;
            }
        }
        return $event->translations[0] ?? null;
    }

    private function resolveCategoryTranslation(
        \Plugin\bbfdesign_events\src\Model\EventCategory $category,
        string $languageIso
    ): ?\Plugin\bbfdesign_events\src\Model\EventCategoryTranslation {
        foreach ($category->translations as $t) {
            if ($t->languageIso === $languageIso) {
                return $t;
            }
        }
        foreach ($category->translations as $t) {
            if ($t->languageIso === EventConfig::DEFAULT_LANGUAGE) {
                return $t;
            }
        }
        return $category->translations[0] ?? null;
    }
}
