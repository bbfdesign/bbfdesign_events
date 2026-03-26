<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Controller\Backend;

use JTL\DB\DbInterface;
use JTL\Smarty\JTLSmarty;
use Plugin\bbfdesign_events\src\Config\EventConfig;
use Plugin\bbfdesign_events\src\Enum\EventDateType;
use Plugin\bbfdesign_events\src\Enum\EventStatus;
use Plugin\bbfdesign_events\src\Helper\DateHelper;
use Plugin\bbfdesign_events\src\Model\Event;
use Plugin\bbfdesign_events\src\Model\EventDate;
use Plugin\bbfdesign_events\src\Model\EventTimeSlot;
use Plugin\bbfdesign_events\src\Model\EventTranslation;
use Plugin\bbfdesign_events\src\Repository\EventCategoryRepository;
use Plugin\bbfdesign_events\src\Repository\EventListFilter;
use Plugin\bbfdesign_events\src\Repository\EventRepository;
use Plugin\bbfdesign_events\src\Service\CacheService;
use Plugin\bbfdesign_events\src\Service\EventDateService;
use Plugin\bbfdesign_events\src\Service\EventService;
use Plugin\bbfdesign_events\src\Service\SeoService;

class EventAdminController
{
    private EventService $eventService;
    private EventRepository $eventRepository;
    private EventCategoryRepository $categoryRepository;

    public function __construct(
        private readonly DbInterface $db,
        private readonly JTLSmarty $smarty,
        private readonly string $postURL
    ) {
        $this->eventRepository = new EventRepository($this->db);
        $this->categoryRepository = new EventCategoryRepository($this->db);
        $seoService = new SeoService();
        $dateService = new EventDateService();
        $cacheService = new CacheService(\JTL\Shop::Container()->getCache());
        $this->eventService = new EventService($this->eventRepository, $dateService, $seoService, $cacheService);
    }

    public function dispatch(string $action): void
    {
        match ($action) {
            'create' => $this->prepareForm(0),
            'edit' => $this->prepareForm((int) ($_GET['id'] ?? 0)),
            'save' => $this->handleSave(),
            'delete' => $this->handleDelete(),
            'duplicate' => $this->handleDuplicate(),
            default => $this->prepareList(),
        };
    }

    private function prepareList(): void
    {
        $filter = EventListFilter::fromRequest($_GET, EventConfig::DEFAULT_LANGUAGE);
        $filter->showAllStatuses = true;
        $filter->perPage = 25;

        $result = $this->eventRepository->findByFilter($filter);

        foreach ($result->events as $event) {
            $this->resolveTranslation($event);
        }

        $this->smarty->assign('events', $result->events);
        $this->smarty->assign('pagination', $result);
        $this->smarty->assign('filter', $filter);
        $this->smarty->assign('statuses', EventStatus::cases());
    }

    private function prepareForm(int $eventId): void
    {
        $languages = $this->getAvailableLanguages();
        $categories = $this->categoryRepository->findAll(false);

        foreach ($categories as $cat) {
            foreach ($cat->translations as $t) {
                if ($t->languageIso === EventConfig::DEFAULT_LANGUAGE) {
                    $cat->translation = $t;
                    break;
                }
            }
        }

        if ($eventId > 0) {
            $event = $this->eventRepository->findById($eventId);
            if ($event === null) {
                header('Location: ' . $this->buildUrl('events') . '&error=notfound');
                exit;
            }
            $this->resolveTranslation($event);
        } else {
            $event = new Event();
        }

        $dates = [];
        if ($eventId > 0) {
            $dateRows = $this->db->getObjects(
                'SELECT * FROM bbf_event_dates WHERE event_id = :eid ORDER BY sort_order, date_start',
                ['eid' => $eventId]
            );
            foreach ($dateRows as $row) {
                $d = new EventDate();
                $d->id = (int) $row->id;
                $d->eventId = (int) $row->event_id;
                $d->dateStart = DateHelper::parseDate($row->date_start) ?? new \DateTimeImmutable();
                $d->dateEnd = DateHelper::parseDate($row->date_end);
                $d->isAllday = (bool) $row->is_allday;
                $d->sortOrder = (int) $row->sort_order;

                $slotRows = $this->db->getObjects(
                    'SELECT * FROM bbf_event_timeslots WHERE event_date_id = :did ORDER BY sort_order',
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
                $dates[] = $d;
            }
        }

        $assignedCategoryIds = [];
        if ($eventId > 0) {
            $catRows = $this->db->getObjects(
                'SELECT category_id FROM bbf_event_category_mapping WHERE event_id = :eid',
                ['eid' => $eventId]
            );
            $assignedCategoryIds = array_map(fn($r) => (int) $r->category_id, $catRows);
        }

        $this->smarty->assign('event', $event);
        $this->smarty->assign('dates', $dates);
        $this->smarty->assign('languages', $languages);
        $this->smarty->assign('categories', $categories);
        $this->smarty->assign('assignedCategoryIds', $assignedCategoryIds);
        $this->smarty->assign('statuses', EventStatus::cases());
        $this->smarty->assign('eventTypes', EventDateType::cases());
        $this->smarty->assign('isEdit', $eventId > 0);
        $this->smarty->assign('activePage', 'event_edit');
    }

    private function handleSave(): void
    {
        $eventId = (int) ($_POST['event_id'] ?? 0);
        $isNew = $eventId === 0;

        $event = $isNew ? new Event() : $this->eventRepository->findById($eventId);
        if ($event === null) {
            header('Location: ' . $this->buildUrl('events') . '&error=notfound');
            exit;
        }

        $event->status = EventStatus::from($_POST['status'] ?? 'draft');
        $event->slug = ($_POST['slug'] ?? '') !== '' ? $_POST['slug'] : '';
        $event->heroImage = ($_POST['hero_image'] ?? '') !== '' ? $_POST['hero_image'] : null;
        $event->eventType = EventDateType::from($_POST['event_type'] ?? 'single');
        $event->isFeatured = isset($_POST['is_featured']);
        $event->sortOrder = (int) ($_POST['sort_order'] ?? 0);
        $event->publishFrom = DateHelper::parseDateTime($_POST['publish_from'] ?? null);
        $event->publishTo = DateHelper::parseDateTime($_POST['publish_to'] ?? null);

        $event->translations = [];
        foreach ($this->getAvailableLanguages() as $lang) {
            $iso = $lang['iso'];
            $prefix = 'trans_' . $iso . '_';
            if (empty($_POST[$prefix . 'title'])) {
                continue;
            }

            $t = new EventTranslation();
            $t->eventId = $eventId;
            $t->languageIso = $iso;
            $t->title = trim($_POST[$prefix . 'title']);
            $t->subtitle = ($_POST[$prefix . 'subtitle'] ?? '') !== '' ? $_POST[$prefix . 'subtitle'] : null;
            $t->teaser = ($_POST[$prefix . 'teaser'] ?? '') !== '' ? $_POST[$prefix . 'teaser'] : null;
            $t->description = ($_POST[$prefix . 'description'] ?? '') !== '' ? $_POST[$prefix . 'description'] : null;
            $t->slugLocalized = ($_POST[$prefix . 'slug_localized'] ?? '') !== '' ? $_POST[$prefix . 'slug_localized'] : null;
            $t->metaTitle = ($_POST[$prefix . 'meta_title'] ?? '') !== '' ? $_POST[$prefix . 'meta_title'] : null;
            $t->metaDescription = ($_POST[$prefix . 'meta_description'] ?? '') !== '' ? $_POST[$prefix . 'meta_description'] : null;
            $t->ogTitle = ($_POST[$prefix . 'og_title'] ?? '') !== '' ? $_POST[$prefix . 'og_title'] : null;
            $t->ogDescription = ($_POST[$prefix . 'og_description'] ?? '') !== '' ? $_POST[$prefix . 'og_description'] : null;
            $t->ogImage = ($_POST[$prefix . 'og_image'] ?? '') !== '' ? $_POST[$prefix . 'og_image'] : null;

            if (!$isNew) {
                $existing = $this->db->getSingleObject(
                    'SELECT id FROM bbf_events_translation WHERE event_id = :eid AND language_iso = :lang',
                    ['eid' => $eventId, 'lang' => $iso]
                );
                if ($existing) {
                    $t->id = (int) $existing->id;
                }
            }
            $event->translations[] = $t;
        }

        $savedId = $this->eventService->saveEvent($event);
        $this->saveDates($savedId);

        $categoryIds = array_map('intval', $_POST['categories'] ?? []);
        $this->eventService->syncCategories($savedId, $categoryIds);

        $msg = $isNew ? 'created' : 'updated';
        header('Location: ' . $this->buildUrl('events', 'edit', $savedId) . '&msg=' . $msg);
        exit;
    }

    private function saveDates(int $eventId): void
    {
        $this->db->executeQuery('DELETE FROM bbf_event_dates WHERE event_id = :eid', ['eid' => $eventId]);

        $dateStarts = $_POST['date_start'] ?? [];
        $dateEnds = $_POST['date_end'] ?? [];
        $isAldays = $_POST['date_allday'] ?? [];

        foreach ($dateStarts as $i => $startStr) {
            if ($startStr === '') {
                continue;
            }
            $dateId = $this->db->insert('bbf_event_dates', (object) [
                'event_id' => $eventId,
                'date_start' => $startStr,
                'date_end' => ($dateEnds[$i] ?? '') !== '' ? $dateEnds[$i] : null,
                'is_allday' => isset($isAldays[$i]) ? 1 : 0,
                'sort_order' => $i,
            ]);

            foreach ($_POST['timeslot_start'][$i] ?? [] as $j => $slotStart) {
                if ($slotStart === '') {
                    continue;
                }
                $this->db->insert('bbf_event_timeslots', (object) [
                    'event_date_id' => $dateId,
                    'time_start' => $slotStart,
                    'time_end' => ($_POST['timeslot_end'][$i][$j] ?? '') !== '' ? $_POST['timeslot_end'][$i][$j] : null,
                    'label' => ($_POST['timeslot_label'][$i][$j] ?? '') !== '' ? $_POST['timeslot_label'][$i][$j] : null,
                    'sort_order' => $j,
                ]);
            }
        }
    }

    private function handleDelete(): void
    {
        $eventId = (int) ($_GET['id'] ?? 0);
        if ($eventId > 0) {
            $this->eventService->deleteEvent($eventId);
        }
        header('Location: ' . $this->buildUrl('events') . '&msg=deleted');
        exit;
    }

    private function handleDuplicate(): void
    {
        $eventId = (int) ($_GET['id'] ?? 0);
        $original = $this->eventRepository->findById($eventId);
        if ($original === null) {
            header('Location: ' . $this->buildUrl('events') . '&error=notfound');
            exit;
        }

        $clone = new Event();
        $clone->status = EventStatus::DRAFT;
        $clone->heroImage = $original->heroImage;
        $clone->eventType = $original->eventType;
        $clone->isFeatured = false;
        $clone->sortOrder = $original->sortOrder;

        foreach ($original->translations as $t) {
            $ct = new EventTranslation();
            $ct->languageIso = $t->languageIso;
            $ct->title = $t->title . ' (Kopie)';
            $ct->subtitle = $t->subtitle;
            $ct->teaser = $t->teaser;
            $ct->description = $t->description;
            $clone->translations[] = $ct;
        }

        $newId = $this->eventService->saveEvent($clone);

        $catIds = $this->db->getObjects(
            'SELECT category_id FROM bbf_event_category_mapping WHERE event_id = :eid',
            ['eid' => $eventId]
        );
        foreach ($catIds as $row) {
            $this->db->insert('bbf_event_category_mapping', (object) [
                'event_id' => $newId,
                'category_id' => (int) $row->category_id,
            ]);
        }

        header('Location: ' . $this->buildUrl('events', 'edit', $newId) . '&msg=duplicated');
        exit;
    }

    private function resolveTranslation(Event $event): void
    {
        foreach ($event->translations as $t) {
            if ($t->languageIso === EventConfig::DEFAULT_LANGUAGE) {
                $event->translation = $t;
                return;
            }
        }
        if (!empty($event->translations)) {
            $event->translation = $event->translations[0];
        }
    }

    private function getAvailableLanguages(): array
    {
        $rows = $this->db->getObjects(
            "SELECT cISO as iso, cNameDeutsch as name FROM tsprache WHERE active = 1 ORDER BY cISO"
        );
        $languages = [];
        foreach ($rows as $row) {
            $languages[] = ['iso' => $row->iso, 'name' => $row->name];
        }
        return $languages ?: [['iso' => 'ger', 'name' => 'Deutsch']];
    }

    private function buildUrl(string $page, string $action = 'list', ?int $id = null): string
    {
        $url = $this->postURL . '&bbf_page=' . $page;
        if ($action !== 'list') {
            $url .= '&action=' . $action;
        }
        if ($id !== null) {
            $url .= '&id=' . $id;
        }
        return $url;
    }
}
