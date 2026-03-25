<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Controller\Backend;

use JTL\DB\DbInterface;
use JTL\Shop;
use Plugin\bbfdesign_events\src\Config\EventConfig;
use Plugin\bbfdesign_events\src\Enum\EventDateType;
use Plugin\bbfdesign_events\src\Enum\EventStatus;
use Plugin\bbfdesign_events\src\Helper\DateHelper;
use Plugin\bbfdesign_events\src\Helper\SlugHelper;
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
    private DbInterface $db;
    private EventService $eventService;
    private EventRepository $eventRepository;
    private EventCategoryRepository $categoryRepository;
    private string $templatePath;

    public function __construct()
    {
        $this->db = Shop::Container()->getDB();
        $cache = Shop::Container()->getCache();

        $this->eventRepository = new EventRepository($this->db);
        $this->categoryRepository = new EventCategoryRepository($this->db);
        $seoService = new SeoService();
        $dateService = new EventDateService();
        $cacheService = new CacheService($cache);

        $this->eventService = new EventService($this->eventRepository, $dateService, $seoService, $cacheService);
        $this->templatePath = EventConfig::getPluginPath() . 'adminmenu/templates/events/';
    }

    public function dispatch(): void
    {
        $action = $_GET['action'] ?? 'list';
        $smarty = Shop::Smarty();

        match ($action) {
            'create' => $this->showForm($smarty),
            'edit' => $this->showForm($smarty, (int) ($_GET['id'] ?? 0)),
            'save' => $this->save($smarty),
            'delete' => $this->delete($smarty),
            'duplicate' => $this->duplicate($smarty),
            default => $this->showList($smarty),
        };
    }

    private function showList(\Smarty $smarty): void
    {
        $filter = EventListFilter::fromRequest($_GET, EventConfig::DEFAULT_LANGUAGE);
        $filter->status = null; // Show all statuses in admin
        $filter->perPage = 25;

        // Remove publish constraints for admin
        $result = $this->eventRepository->findByFilter($filter);

        // Hydrate translations
        foreach ($result->events as $event) {
            foreach ($event->translations as $t) {
                if ($t->languageIso === EventConfig::DEFAULT_LANGUAGE) {
                    $event->translation = $t;
                    break;
                }
            }
            if ($event->translation === null && !empty($event->translations)) {
                $event->translation = $event->translations[0];
            }
        }

        $smarty->assign('events', $result->events);
        $smarty->assign('pagination', $result);
        $smarty->assign('filter', $filter);
        $smarty->assign('statuses', EventStatus::cases());
        $smarty->display($this->templatePath . 'list.tpl');
    }

    private function showForm(\Smarty $smarty, int $eventId = 0): void
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
            $event = $this->eventService->getEventById($eventId, EventConfig::DEFAULT_LANGUAGE);
            if ($event === null) {
                header('Location: ?action=list&error=notfound');
                return;
            }
        } else {
            $event = new Event();
        }

        // Load dates with timeslots
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

        // Assigned category IDs
        $assignedCategoryIds = [];
        if ($eventId > 0) {
            $catRows = $this->db->getObjects(
                'SELECT category_id FROM bbf_event_category_mapping WHERE event_id = :eid',
                ['eid' => $eventId]
            );
            $assignedCategoryIds = array_map(fn($r) => (int) $r->category_id, $catRows);
        }

        $smarty->assign('event', $event);
        $smarty->assign('dates', $dates);
        $smarty->assign('languages', $languages);
        $smarty->assign('categories', $categories);
        $smarty->assign('assignedCategoryIds', $assignedCategoryIds);
        $smarty->assign('statuses', EventStatus::cases());
        $smarty->assign('eventTypes', EventDateType::cases());
        $smarty->assign('isEdit', $eventId > 0);
        $smarty->display($this->templatePath . 'edit.tpl');
    }

    private function save(\Smarty $smarty): void
    {
        $eventId = (int) ($_POST['event_id'] ?? 0);
        $isNew = $eventId === 0;

        $event = $isNew ? new Event() : $this->eventRepository->findById($eventId);
        if ($event === null) {
            header('Location: ?action=list&error=notfound');
            return;
        }

        // Basic fields
        $event->status = EventStatus::from($_POST['status'] ?? 'draft');
        $event->slug = $_POST['slug'] !== '' ? $_POST['slug'] : '';
        $event->heroImage = $_POST['hero_image'] !== '' ? $_POST['hero_image'] : null;
        $event->eventType = EventDateType::from($_POST['event_type'] ?? 'single');
        $event->isFeatured = isset($_POST['is_featured']);
        $event->sortOrder = (int) ($_POST['sort_order'] ?? 0);
        $event->publishFrom = DateHelper::parseDateTime($_POST['publish_from'] ?? null);
        $event->publishTo = DateHelper::parseDateTime($_POST['publish_to'] ?? null);

        // Translations
        $event->translations = [];
        $languages = $this->getAvailableLanguages();
        foreach ($languages as $lang) {
            $iso = $lang['iso'];
            $prefix = 'trans_' . $iso . '_';

            if (empty($_POST[$prefix . 'title'])) {
                continue;
            }

            $t = new EventTranslation();
            $t->eventId = $eventId;
            $t->languageIso = $iso;
            $t->title = trim($_POST[$prefix . 'title']);
            $t->subtitle = $_POST[$prefix . 'subtitle'] !== '' ? $_POST[$prefix . 'subtitle'] : null;
            $t->teaser = $_POST[$prefix . 'teaser'] !== '' ? $_POST[$prefix . 'teaser'] : null;
            $t->description = $_POST[$prefix . 'description'] !== '' ? $_POST[$prefix . 'description'] : null;
            $t->slugLocalized = $_POST[$prefix . 'slug_localized'] !== '' ? $_POST[$prefix . 'slug_localized'] : null;
            $t->metaTitle = $_POST[$prefix . 'meta_title'] !== '' ? $_POST[$prefix . 'meta_title'] : null;
            $t->metaDescription = $_POST[$prefix . 'meta_description'] !== '' ? $_POST[$prefix . 'meta_description'] : null;
            $t->ogTitle = $_POST[$prefix . 'og_title'] !== '' ? $_POST[$prefix . 'og_title'] : null;
            $t->ogDescription = $_POST[$prefix . 'og_description'] !== '' ? $_POST[$prefix . 'og_description'] : null;
            $t->ogImage = $_POST[$prefix . 'og_image'] !== '' ? $_POST[$prefix . 'og_image'] : null;

            // Load existing translation ID
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

        // Save event
        $savedId = $this->eventService->saveEvent($event);

        // Save dates
        $this->saveDates($savedId);

        // Sync categories
        $categoryIds = array_map('intval', $_POST['categories'] ?? []);
        $this->eventService->syncCategories($savedId, $categoryIds);

        $msg = $isNew ? 'created' : 'updated';
        header('Location: ?action=edit&id=' . $savedId . '&msg=' . $msg);
    }

    private function saveDates(int $eventId): void
    {
        // Delete existing dates (cascade deletes timeslots)
        $this->db->executeQuery(
            'DELETE FROM bbf_event_dates WHERE event_id = :eid',
            ['eid' => $eventId]
        );

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

            // Timeslots for this date
            $slotStarts = $_POST['timeslot_start'][$i] ?? [];
            $slotEnds = $_POST['timeslot_end'][$i] ?? [];
            $slotLabels = $_POST['timeslot_label'][$i] ?? [];

            foreach ($slotStarts as $j => $slotStart) {
                if ($slotStart === '') {
                    continue;
                }

                $this->db->insert('bbf_event_timeslots', (object) [
                    'event_date_id' => $dateId,
                    'time_start' => $slotStart,
                    'time_end' => ($slotEnds[$j] ?? '') !== '' ? $slotEnds[$j] : null,
                    'label' => ($slotLabels[$j] ?? '') !== '' ? $slotLabels[$j] : null,
                    'sort_order' => $j,
                ]);
            }
        }
    }

    private function delete(\Smarty $smarty): void
    {
        $eventId = (int) ($_GET['id'] ?? 0);
        if ($eventId > 0) {
            $this->eventService->deleteEvent($eventId);
        }
        header('Location: ?action=list&msg=deleted');
    }

    private function duplicate(\Smarty $smarty): void
    {
        $eventId = (int) ($_GET['id'] ?? 0);
        $original = $this->eventRepository->findById($eventId);

        if ($original === null) {
            header('Location: ?action=list&error=notfound');
            return;
        }

        $clone = new Event();
        $clone->status = EventStatus::DRAFT;
        $clone->heroImage = $original->heroImage;
        $clone->eventType = $original->eventType;
        $clone->isFeatured = false;
        $clone->sortOrder = $original->sortOrder;

        // Clone translations with modified title
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

        // Clone category mappings
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

        header('Location: ?action=edit&id=' . $newId . '&msg=duplicated');
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

        if (empty($languages)) {
            $languages[] = ['iso' => 'ger', 'name' => 'Deutsch'];
        }

        return $languages;
    }
}
