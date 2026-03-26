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

        // ── Sub-Modul Daten laden (nur bei Edit) ──────────
        $programEntries = [];
        $eventTickets = [];
        $allPartners = [];
        $assignedPartnerIds = [];
        $partnerCategories = [];
        $allKnowledgeItems = [];
        $assignedKnowledgeIds = [];
        $allAreaMaps = [];
        $assignedAreaIds = [];
        $eventMedia = [];
        $eventLinks = [];

        if ($eventId > 0) {
            $lang = EventConfig::DEFAULT_LANGUAGE;

            // Programm
            $programEntries = $this->db->getObjects(
                'SELECT pe.*, pet.title as prog_title, pet.description as prog_desc, pet.speaker_title
                 FROM bbf_event_program_entries pe
                 LEFT JOIN bbf_event_program_entries_translation pet ON pe.id = pet.entry_id AND pet.language_iso = :lang
                 WHERE pe.event_id = :eid ORDER BY pe.sort_order',
                ['eid' => $eventId, 'lang' => $lang]
            );

            // Tickets
            $eventTickets = $this->db->getObjects(
                'SELECT t.*, tt.name as ticket_name, tt.description as ticket_desc, tt.cta_label, tt.hint
                 FROM bbf_event_tickets t
                 LEFT JOIN bbf_event_tickets_translation tt ON t.id = tt.ticket_id AND tt.language_iso = :lang
                 WHERE t.event_id = :eid ORDER BY t.sort_order',
                ['eid' => $eventId, 'lang' => $lang]
            );

            // Partner (alle + zugewiesene)
            $allPartners = $this->db->getObjects(
                'SELECT p.*, pt.name FROM bbf_partners p
                 LEFT JOIN bbf_partners_translation pt ON p.id = pt.partner_id AND pt.language_iso = :lang
                 WHERE p.is_active = 1 ORDER BY p.sort_order',
                ['lang' => $lang]
            );
            $assignedPartnerRows = $this->db->getObjects(
                'SELECT partner_id, category_id FROM bbf_event_partner_mapping WHERE event_id = :eid',
                ['eid' => $eventId]
            );
            $assignedPartnerIds = array_map(fn($r) => (int) $r->partner_id, $assignedPartnerRows);
            $partnerCategories = $this->db->getObjects(
                'SELECT pc.*, pct.name FROM bbf_partner_categories pc
                 LEFT JOIN bbf_partner_categories_translation pct ON pc.id = pct.category_id AND pct.language_iso = :lang
                 ORDER BY pc.sort_order',
                ['lang' => $lang]
            );

            // Knowledge (alle + zugewiesene)
            $allKnowledgeItems = $this->db->getObjects(
                'SELECT ki.*, kit.title FROM bbf_knowledge_items ki
                 LEFT JOIN bbf_knowledge_items_translation kit ON ki.id = kit.item_id AND kit.language_iso = :lang
                 ORDER BY ki.sort_order',
                ['lang' => $lang]
            );
            $assignedKnowledgeRows = $this->db->getObjects(
                'SELECT item_id FROM bbf_event_knowledge_mapping WHERE event_id = :eid',
                ['eid' => $eventId]
            );
            $assignedKnowledgeIds = array_map(fn($r) => (int) $r->item_id, $assignedKnowledgeRows);

            // Areas (alle + zugewiesene)
            $allAreaMaps = $this->db->getObjects(
                'SELECT am.*, amt.title FROM bbf_area_maps am
                 LEFT JOIN bbf_area_maps_translation amt ON am.id = amt.map_id AND amt.language_iso = :lang
                 ORDER BY am.slug',
                ['lang' => $lang]
            );
            $assignedAreaRows = $this->db->getObjects(
                'SELECT map_id FROM bbf_event_area_mapping WHERE event_id = :eid',
                ['eid' => $eventId]
            );
            $assignedAreaIds = array_map(fn($r) => (int) $r->map_id, $assignedAreaRows);

            // Media
            $eventMedia = $this->db->getObjects(
                'SELECT * FROM bbf_event_media WHERE event_id = :eid ORDER BY sort_order',
                ['eid' => $eventId]
            );

            // Links
            $eventLinks = $this->db->getObjects(
                'SELECT el.*, elt.label, elt.description as link_desc
                 FROM bbf_event_links el
                 LEFT JOIN bbf_event_links_translation elt ON el.id = elt.link_id AND elt.language_iso = :lang
                 WHERE el.event_id = :eid ORDER BY el.sort_order',
                ['eid' => $eventId, 'lang' => $lang]
            );
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

        // Sub-Modul Daten
        $this->smarty->assign('programEntries', $programEntries);
        $this->smarty->assign('eventTickets', $eventTickets);
        $this->smarty->assign('allPartners', $allPartners);
        $this->smarty->assign('assignedPartnerIds', $assignedPartnerIds);
        $this->smarty->assign('partnerCategories', $partnerCategories);
        $this->smarty->assign('allKnowledgeItems', $allKnowledgeItems);
        $this->smarty->assign('assignedKnowledgeIds', $assignedKnowledgeIds);
        $this->smarty->assign('allAreaMaps', $allAreaMaps);
        $this->smarty->assign('assignedAreaIds', $assignedAreaIds);
        $this->smarty->assign('eventMedia', $eventMedia);
        $this->smarty->assign('eventLinks', $eventLinks);
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

        // ── Sub-Module speichern ──────────────────────────
        $this->saveProgram($savedId);
        $this->saveTickets($savedId);
        $this->savePartners($savedId);
        $this->saveKnowledge($savedId);
        $this->saveAreas($savedId);
        $this->saveMedia($savedId);
        $this->saveLinks($savedId);

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

    private function saveProgram(int $eventId): void
    {
        // Delete existing, re-insert from POST
        $this->db->executeQuery('DELETE FROM bbf_event_program_entries WHERE event_id = :eid', ['eid' => $eventId]);

        $programs = $_POST['program'] ?? [];
        foreach ($programs as $i => $prog) {
            $title = $prog['title_ger'] ?? '';
            if ($title === '') {
                continue;
            }

            $entryId = $this->db->insert('bbf_event_program_entries', (object) [
                'event_id' => $eventId,
                'event_date_id' => ($prog['event_date_id'] ?? '') !== '' ? (int) $prog['event_date_id'] : null,
                'category_id' => ($prog['category_id'] ?? '') !== '' ? (int) $prog['category_id'] : null,
                'time_start' => ($prog['time_start'] ?? '') !== '' ? $prog['time_start'] : null,
                'time_end' => ($prog['time_end'] ?? '') !== '' ? $prog['time_end'] : null,
                'speaker_name' => ($prog['speaker_name'] ?? '') !== '' ? $prog['speaker_name'] : null,
                'speaker_image' => ($prog['speaker_image'] ?? '') !== '' ? $prog['speaker_image'] : null,
                'link_url' => ($prog['link_url'] ?? '') !== '' ? $prog['link_url'] : null,
                'sort_order' => $i,
                'is_highlight' => isset($prog['is_highlight']) ? 1 : 0,
            ]);

            $this->db->insert('bbf_event_program_entries_translation', (object) [
                'entry_id' => $entryId,
                'language_iso' => 'ger',
                'title' => trim($title),
                'description' => ($prog['description_ger'] ?? '') !== '' ? $prog['description_ger'] : null,
                'speaker_title' => ($prog['speaker_title_ger'] ?? '') !== '' ? $prog['speaker_title_ger'] : null,
            ]);
        }
    }

    private function saveTickets(int $eventId): void
    {
        $this->db->executeQuery('DELETE FROM bbf_event_tickets WHERE event_id = :eid', ['eid' => $eventId]);

        $tickets = $_POST['tickets'] ?? [];
        foreach ($tickets as $i => $ticket) {
            $name = $ticket['name_ger'] ?? '';
            if ($name === '') {
                continue;
            }

            $ticketId = $this->db->insert('bbf_event_tickets', (object) [
                'event_id' => $eventId,
                'category_id' => ($ticket['category_id'] ?? '') !== '' ? (int) $ticket['category_id'] : null,
                'source_type' => $ticket['source_type'] ?? 'external',
                'wawi_article_id' => ($ticket['wawi_article_id'] ?? '') !== '' ? (int) $ticket['wawi_article_id'] : null,
                'wawi_article_no' => ($ticket['wawi_article_no'] ?? '') !== '' ? $ticket['wawi_article_no'] : null,
                'external_url' => ($ticket['external_url'] ?? '') !== '' ? $ticket['external_url'] : null,
                'external_provider' => ($ticket['external_provider'] ?? '') !== '' ? $ticket['external_provider'] : null,
                'price_gross' => ($ticket['price_gross'] ?? '') !== '' ? (float) $ticket['price_gross'] : null,
                'tax_rate' => ($ticket['tax_rate'] ?? '') !== '' ? (float) $ticket['tax_rate'] : null,
                'max_quantity' => ($ticket['max_quantity'] ?? '') !== '' ? (int) $ticket['max_quantity'] : null,
                'available_from' => ($ticket['available_from'] ?? '') !== '' ? $ticket['available_from'] : null,
                'available_to' => ($ticket['available_to'] ?? '') !== '' ? $ticket['available_to'] : null,
                'is_active' => isset($ticket['is_active']) ? 1 : 1,
                'sort_order' => (int) ($ticket['sort_order'] ?? $i),
            ]);

            $this->db->insert('bbf_event_tickets_translation', (object) [
                'ticket_id' => $ticketId,
                'language_iso' => 'ger',
                'name' => trim($name),
                'description' => ($ticket['description_ger'] ?? '') !== '' ? $ticket['description_ger'] : null,
                'cta_label' => ($ticket['cta_label_ger'] ?? '') !== '' ? $ticket['cta_label_ger'] : null,
                'hint' => ($ticket['hint_ger'] ?? '') !== '' ? $ticket['hint_ger'] : null,
            ]);
        }
    }

    private function savePartners(int $eventId): void
    {
        $this->db->executeQuery('DELETE FROM bbf_event_partner_mapping WHERE event_id = :eid', ['eid' => $eventId]);

        $partners = $_POST['event_partners'] ?? [];
        $sort = 0;
        foreach ($partners as $partnerId => $mapping) {
            if (!isset($mapping['partner_id'])) {
                continue;
            }
            $this->db->insert('bbf_event_partner_mapping', (object) [
                'event_id' => $eventId,
                'partner_id' => (int) $mapping['partner_id'],
                'category_id' => ($mapping['category_id'] ?? '') !== '' ? (int) $mapping['category_id'] : null,
                'sort_order' => $sort++,
                'is_visible' => 1,
            ]);
        }
    }

    private function saveKnowledge(int $eventId): void
    {
        $this->db->executeQuery('DELETE FROM bbf_event_knowledge_mapping WHERE event_id = :eid', ['eid' => $eventId]);

        $items = $_POST['knowledge_items'] ?? [];
        foreach ($items as $i => $itemId) {
            $this->db->insert('bbf_event_knowledge_mapping', (object) [
                'event_id' => $eventId,
                'item_id' => (int) $itemId,
                'sort_order' => $i,
            ]);
        }
    }

    private function saveAreas(int $eventId): void
    {
        $this->db->executeQuery('DELETE FROM bbf_event_area_mapping WHERE event_id = :eid', ['eid' => $eventId]);

        $maps = $_POST['area_maps'] ?? [];
        foreach ($maps as $i => $mapId) {
            $this->db->insert('bbf_event_area_mapping', (object) [
                'event_id' => $eventId,
                'map_id' => (int) $mapId,
                'sort_order' => $i,
            ]);
        }
    }

    private function saveMedia(int $eventId): void
    {
        $this->db->executeQuery('DELETE FROM bbf_event_media WHERE event_id = :eid', ['eid' => $eventId]);

        $mediaItems = $_POST['media'] ?? [];
        foreach ($mediaItems as $i => $media) {
            $filePath = $media['file_path'] ?? '';
            if ($filePath === '') {
                continue;
            }

            $mediaType = $media['media_type'] ?? 'image';
            $isExternal = in_array($mediaType, ['youtube', 'vimeo'], true);

            $this->db->insert('bbf_event_media', (object) [
                'event_id' => $eventId,
                'media_type' => $mediaType,
                'file_path' => !$isExternal ? $filePath : null,
                'external_url' => $isExternal ? $filePath : null,
                'alt_text' => ($media['alt_text'] ?? '') !== '' ? $media['alt_text'] : null,
                'title' => ($media['title'] ?? '') !== '' ? $media['title'] : null,
                'sort_order' => $i,
                'context' => $media['context'] ?? 'default',
            ]);
        }
    }

    private function saveLinks(int $eventId): void
    {
        // Delete existing links + translations (cascade)
        $this->db->executeQuery('DELETE FROM bbf_event_links WHERE event_id = :eid', ['eid' => $eventId]);

        $links = $_POST['links'] ?? [];
        foreach ($links as $i => $link) {
            $url = $link['target_url'] ?? '';
            $label = $link['label_ger'] ?? '';
            if ($url === '' && $label === '') {
                continue;
            }

            $linkId = $this->db->insert('bbf_event_links', (object) [
                'event_id' => $eventId,
                'link_type' => $link['link_type'] ?? 'external',
                'target_url' => $url !== '' ? $url : null,
                'target_id' => ($link['target_id'] ?? '') !== '' ? (int) $link['target_id'] : null,
                'sort_order' => $i,
                'context' => $link['context'] ?? 'related',
            ]);

            if ($label !== '') {
                $this->db->insert('bbf_event_links_translation', (object) [
                    'link_id' => $linkId,
                    'language_iso' => 'ger',
                    'label' => trim($label),
                    'description' => ($link['description_ger'] ?? '') !== '' ? $link['description_ger'] : null,
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
