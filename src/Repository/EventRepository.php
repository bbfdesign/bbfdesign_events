<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Repository;

use JTL\DB\DbInterface;
use Plugin\bbfdesign_events\src\Enum\EventDateType;
use Plugin\bbfdesign_events\src\Enum\EventStatus;
use Plugin\bbfdesign_events\src\Helper\DateHelper;
use Plugin\bbfdesign_events\src\Model\Event;
use Plugin\bbfdesign_events\src\Model\EventCategory;
use Plugin\bbfdesign_events\src\Model\EventCategoryTranslation;
use Plugin\bbfdesign_events\src\Model\EventDate;
use Plugin\bbfdesign_events\src\Model\EventMedia;
use Plugin\bbfdesign_events\src\Model\EventTimeSlot;
use Plugin\bbfdesign_events\src\Model\EventTranslation;

class EventRepository
{
    public function __construct(
        private readonly DbInterface $db
    ) {}

    public function findById(int $id): ?Event
    {
        $row = $this->db->getSingleObject(
            'SELECT * FROM bbf_events WHERE id = :id',
            ['id' => $id]
        );

        if ($row === null) {
            return null;
        }

        $event = $this->hydrateEvent($row);
        $this->loadRelations($event);

        return $event;
    }

    public function findBySlug(string $slug): ?Event
    {
        $row = $this->db->getSingleObject(
            'SELECT e.* FROM bbf_events e
             LEFT JOIN bbf_events_translation et ON e.id = et.event_id
             WHERE e.slug = :slug OR et.slug_localized = :slug
             LIMIT 1',
            ['slug' => $slug]
        );

        if ($row === null) {
            return null;
        }

        $event = $this->hydrateEvent($row);
        $this->loadRelations($event);

        return $event;
    }

    /**
     * @return Event[]
     */
    public function findByFilter(EventListFilter $filter): EventListResult
    {
        $where = ['1=1'];
        $params = [];

        if ($filter->status !== null) {
            $where[] = 'e.status = :status';
            $params['status'] = $filter->status->value;
        } else {
            $where[] = "e.status = 'published'";
        }

        if ($filter->categorySlug !== null) {
            $where[] = 'EXISTS (
                SELECT 1 FROM bbf_event_category_mapping ecm
                JOIN bbf_event_categories ec ON ecm.category_id = ec.id
                WHERE ecm.event_id = e.id AND ec.slug = :cat_slug
            )';
            $params['cat_slug'] = $filter->categorySlug;
        }

        if ($filter->isFeatured !== null) {
            $where[] = 'e.is_featured = :featured';
            $params['featured'] = $filter->isFeatured ? 1 : 0;
        }

        if ($filter->temporalStatus === 'upcoming') {
            $where[] = 'EXISTS (
                SELECT 1 FROM bbf_event_dates ed
                WHERE ed.event_id = e.id AND ed.date_start >= CURDATE()
            )';
        } elseif ($filter->temporalStatus === 'past') {
            $where[] = 'NOT EXISTS (
                SELECT 1 FROM bbf_event_dates ed
                WHERE ed.event_id = e.id AND COALESCE(ed.date_end, ed.date_start) >= CURDATE()
            )';
        }

        if ($filter->dateFrom !== null) {
            $where[] = 'EXISTS (
                SELECT 1 FROM bbf_event_dates ed
                WHERE ed.event_id = e.id AND COALESCE(ed.date_end, ed.date_start) >= :date_from
            )';
            $params['date_from'] = $filter->dateFrom->format('Y-m-d');
        }

        if ($filter->dateTo !== null) {
            $where[] = 'EXISTS (
                SELECT 1 FROM bbf_event_dates ed
                WHERE ed.event_id = e.id AND ed.date_start <= :date_to
            )';
            $params['date_to'] = $filter->dateTo->format('Y-m-d');
        }

        if ($filter->searchQuery !== null && $filter->searchQuery !== '') {
            $where[] = 'EXISTS (
                SELECT 1 FROM bbf_events_translation et2
                WHERE et2.event_id = e.id
                AND (et2.title LIKE :search OR et2.teaser LIKE :search OR et2.description LIKE :search)
            )';
            $params['search'] = '%' . $filter->searchQuery . '%';
        }

        // Sichtbarkeit (publish_from/to)
        $where[] = '(e.publish_from IS NULL OR e.publish_from <= NOW())';
        $where[] = '(e.publish_to IS NULL OR e.publish_to >= NOW())';

        $whereClause = implode(' AND ', $where);

        // Count
        $countRow = $this->db->getSingleObject(
            "SELECT COUNT(DISTINCT e.id) as total FROM bbf_events e WHERE {$whereClause}",
            $params
        );
        $total = (int) ($countRow->total ?? 0);

        // Sort
        $orderBy = match ($filter->sortBy) {
            'date_asc' => '(SELECT MIN(ed.date_start) FROM bbf_event_dates ed WHERE ed.event_id = e.id) ASC',
            'date_desc' => '(SELECT MIN(ed.date_start) FROM bbf_event_dates ed WHERE ed.event_id = e.id) DESC',
            'title' => '(SELECT et.title FROM bbf_events_translation et WHERE et.event_id = e.id LIMIT 1) ASC',
            'featured' => 'e.is_featured DESC, e.sort_order ASC',
            default => '(SELECT MIN(ed.date_start) FROM bbf_event_dates ed WHERE ed.event_id = e.id) ASC',
        };

        $offset = ($filter->page - 1) * $filter->perPage;

        $rows = $this->db->getObjects(
            "SELECT e.* FROM bbf_events e
             WHERE {$whereClause}
             ORDER BY {$orderBy}
             LIMIT :limit OFFSET :offset",
            array_merge($params, ['limit' => $filter->perPage, 'offset' => $offset])
        );

        $events = [];
        foreach ($rows as $row) {
            $event = $this->hydrateEvent($row);
            $this->loadRelations($event);
            $events[] = $event;
        }

        return new EventListResult(
            events: $events,
            total: $total,
            page: $filter->page,
            perPage: $filter->perPage
        );
    }

    public function save(Event $event): int
    {
        if ($event->id > 0) {
            return $this->update($event);
        }
        return $this->insert($event);
    }

    public function delete(int $id): bool
    {
        return $this->db->delete('bbf_events', 'id', $id) > 0;
    }

    public function slugExists(string $slug, ?int $excludeId = null): bool
    {
        $params = ['slug' => $slug];
        $exclude = '';
        if ($excludeId !== null) {
            $exclude = ' AND id != :exclude_id';
            $params['exclude_id'] = $excludeId;
        }

        $row = $this->db->getSingleObject(
            "SELECT COUNT(*) as cnt FROM bbf_events WHERE slug = :slug{$exclude}",
            $params
        );

        return (int) ($row->cnt ?? 0) > 0;
    }

    // ── Translation CRUD ──────────────────────────────────

    public function saveTranslation(EventTranslation $translation): int
    {
        $data = [
            'event_id' => $translation->eventId,
            'language_iso' => $translation->languageIso,
            'title' => $translation->title,
            'subtitle' => $translation->subtitle,
            'teaser' => $translation->teaser,
            'description' => $translation->description,
            'slug_localized' => $translation->slugLocalized,
            'meta_title' => $translation->metaTitle,
            'meta_description' => $translation->metaDescription,
            'og_title' => $translation->ogTitle,
            'og_description' => $translation->ogDescription,
            'og_image' => $translation->ogImage,
        ];

        if ($translation->id > 0) {
            $this->db->update('bbf_events_translation', 'id', $translation->id, (object) $data);
            return $translation->id;
        }

        return $this->db->insert('bbf_events_translation', (object) $data);
    }

    public function deleteTranslation(int $eventId, string $languageIso): bool
    {
        return $this->db->executeQuery(
            'DELETE FROM bbf_events_translation WHERE event_id = :eid AND language_iso = :lang',
            ['eid' => $eventId, 'lang' => $languageIso]
        ) > 0;
    }

    // ── Category Mapping ──────────────────────────────────

    public function syncCategories(int $eventId, array $categoryIds): void
    {
        $this->db->executeQuery(
            'DELETE FROM bbf_event_category_mapping WHERE event_id = :eid',
            ['eid' => $eventId]
        );

        foreach ($categoryIds as $categoryId) {
            $this->db->insert('bbf_event_category_mapping', (object) [
                'event_id' => $eventId,
                'category_id' => (int) $categoryId,
            ]);
        }
    }

    // ── Private Helpers ───────────────────────────────────

    private function insert(Event $event): int
    {
        return $this->db->insert('bbf_events', (object) [
            'status' => $event->status->value,
            'slug' => $event->slug,
            'hero_image' => $event->heroImage,
            'event_type' => $event->eventType->value,
            'is_featured' => $event->isFeatured ? 1 : 0,
            'sort_order' => $event->sortOrder,
            'publish_from' => $event->publishFrom?->format('Y-m-d H:i:s'),
            'publish_to' => $event->publishTo?->format('Y-m-d H:i:s'),
            'created_by' => $event->createdBy,
        ]);
    }

    private function update(Event $event): int
    {
        $this->db->update('bbf_events', 'id', $event->id, (object) [
            'status' => $event->status->value,
            'slug' => $event->slug,
            'hero_image' => $event->heroImage,
            'event_type' => $event->eventType->value,
            'is_featured' => $event->isFeatured ? 1 : 0,
            'sort_order' => $event->sortOrder,
            'publish_from' => $event->publishFrom?->format('Y-m-d H:i:s'),
            'publish_to' => $event->publishTo?->format('Y-m-d H:i:s'),
        ]);

        return $event->id;
    }

    private function hydrateEvent(object $row): Event
    {
        $event = new Event();
        $event->id = (int) $row->id;
        $event->status = EventStatus::from($row->status);
        $event->slug = $row->slug;
        $event->heroImage = $row->hero_image;
        $event->eventType = EventDateType::from($row->event_type);
        $event->isFeatured = (bool) $row->is_featured;
        $event->sortOrder = (int) $row->sort_order;
        $event->publishFrom = DateHelper::parseDateTime($row->publish_from);
        $event->publishTo = DateHelper::parseDateTime($row->publish_to);
        $event->createdAt = DateHelper::parseDateTime($row->created_at) ?? new \DateTimeImmutable();
        $event->updatedAt = DateHelper::parseDateTime($row->updated_at) ?? new \DateTimeImmutable();
        $event->createdBy = $row->created_by !== null ? (int) $row->created_by : null;

        return $event;
    }

    private function loadRelations(Event $event): void
    {
        $this->loadTranslations($event);
        $this->loadDates($event);
        $this->loadCategories($event);
    }

    private function loadTranslations(Event $event): void
    {
        $rows = $this->db->getObjects(
            'SELECT * FROM bbf_events_translation WHERE event_id = :eid',
            ['eid' => $event->id]
        );

        foreach ($rows as $row) {
            $t = new EventTranslation();
            $t->id = (int) $row->id;
            $t->eventId = (int) $row->event_id;
            $t->languageIso = $row->language_iso;
            $t->title = $row->title;
            $t->subtitle = $row->subtitle;
            $t->teaser = $row->teaser;
            $t->description = $row->description;
            $t->slugLocalized = $row->slug_localized;
            $t->metaTitle = $row->meta_title;
            $t->metaDescription = $row->meta_description;
            $t->ogTitle = $row->og_title;
            $t->ogDescription = $row->og_description;
            $t->ogImage = $row->og_image;
            $event->translations[] = $t;
        }
    }

    private function loadDates(Event $event): void
    {
        $rows = $this->db->getObjects(
            'SELECT * FROM bbf_event_dates WHERE event_id = :eid ORDER BY sort_order, date_start',
            ['eid' => $event->id]
        );

        foreach ($rows as $row) {
            $d = new EventDate();
            $d->id = (int) $row->id;
            $d->eventId = (int) $row->event_id;
            $d->dateStart = DateHelper::parseDate($row->date_start) ?? new \DateTimeImmutable();
            $d->dateEnd = DateHelper::parseDate($row->date_end);
            $d->isAllday = (bool) $row->is_allday;
            $d->sortOrder = (int) $row->sort_order;

            // Load timeslots
            $slotRows = $this->db->getObjects(
                'SELECT * FROM bbf_event_timeslots WHERE event_date_id = :did ORDER BY sort_order, time_start',
                ['did' => $d->id]
            );

            foreach ($slotRows as $sr) {
                $ts = new EventTimeSlot();
                $ts->id = (int) $sr->id;
                $ts->eventDateId = (int) $sr->event_date_id;
                $ts->timeStart = DateHelper::parseTime($sr->time_start) ?? new \DateTimeImmutable();
                $ts->timeEnd = DateHelper::parseTime($sr->time_end);
                $ts->label = $sr->label;
                $ts->sortOrder = (int) $sr->sort_order;
                $d->timeSlots[] = $ts;
            }

            $event->dates[] = $d;
        }
    }

    private function loadCategories(Event $event): void
    {
        $rows = $this->db->getObjects(
            'SELECT ec.*, ect.name, ect.description as cat_desc, ect.meta_title, ect.meta_description, ect.language_iso
             FROM bbf_event_category_mapping ecm
             JOIN bbf_event_categories ec ON ecm.category_id = ec.id
             LEFT JOIN bbf_event_categories_translation ect ON ec.id = ect.category_id
             WHERE ecm.event_id = :eid
             ORDER BY ec.sort_order',
            ['eid' => $event->id]
        );

        $categories = [];
        foreach ($rows as $row) {
            $catId = (int) $row->id;

            if (!isset($categories[$catId])) {
                $cat = new EventCategory();
                $cat->id = $catId;
                $cat->slug = $row->slug;
                $cat->parentId = $row->parent_id !== null ? (int) $row->parent_id : null;
                $cat->sortOrder = (int) $row->sort_order;
                $cat->isActive = (bool) $row->is_active;
                $cat->image = $row->image;
                $categories[$catId] = $cat;
            }

            if (isset($row->language_iso) && $row->language_iso !== null) {
                $ct = new EventCategoryTranslation();
                $ct->categoryId = $catId;
                $ct->languageIso = $row->language_iso;
                $ct->name = $row->name ?? '';
                $ct->description = $row->cat_desc;
                $ct->metaTitle = $row->meta_title;
                $ct->metaDescription = $row->meta_description;
                $categories[$catId]->translations[] = $ct;
            }
        }

        $event->categories = array_values($categories);
    }
}
