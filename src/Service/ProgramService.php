<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Service;

use JTL\DB\DbInterface;
use Plugin\bbfdesign_events\src\Config\EventConfig;
use Plugin\bbfdesign_events\src\Helper\DateHelper;
use Plugin\bbfdesign_events\src\Model\Program\ProgramCategory;
use Plugin\bbfdesign_events\src\Model\Program\ProgramCategoryTranslation;
use Plugin\bbfdesign_events\src\Model\Program\ProgramEntry;
use Plugin\bbfdesign_events\src\Model\Program\ProgramEntryTranslation;

class ProgramService
{
    public function __construct(
        private readonly DbInterface $db
    ) {}

    /**
     * @return ProgramEntry[]
     */
    public function getEntriesForEvent(int $eventId, string $languageIso): array
    {
        $rows = $this->db->getObjects(
            'SELECT pe.*, pet.title, pet.description, pet.speaker_title,
                    pc.slug as cat_slug, pc.color as cat_color, pc.icon as cat_icon,
                    pct.name as cat_name
             FROM bbf_event_program_entries pe
             LEFT JOIN bbf_event_program_entries_translation pet
                 ON pe.id = pet.entry_id AND pet.language_iso = :lang
             LEFT JOIN bbf_event_program_categories pc
                 ON pe.category_id = pc.id
             LEFT JOIN bbf_event_program_categories_translation pct
                 ON pc.id = pct.category_id AND pct.language_iso = :lang
             WHERE pe.event_id = :eid
             ORDER BY pe.sort_order, pe.time_start',
            ['eid' => $eventId, 'lang' => $languageIso]
        );

        $entries = [];
        foreach ($rows as $row) {
            $entry = new ProgramEntry();
            $entry->id = (int) $row->id;
            $entry->eventId = (int) $row->event_id;
            $entry->eventDateId = $row->event_date_id !== null ? (int) $row->event_date_id : null;
            $entry->categoryId = $row->category_id !== null ? (int) $row->category_id : null;
            $entry->timeStart = DateHelper::parseTime($row->time_start);
            $entry->timeEnd = DateHelper::parseTime($row->time_end);
            $entry->speakerName = $row->speaker_name;
            $entry->speakerImage = $row->speaker_image;
            $entry->linkUrl = $row->link_url;
            $entry->linkTarget = $row->link_target ?? '_self';
            $entry->sortOrder = (int) $row->sort_order;
            $entry->isHighlight = (bool) $row->is_highlight;

            // Translation
            if ($row->title !== null) {
                $t = new ProgramEntryTranslation();
                $t->entryId = $entry->id;
                $t->languageIso = $languageIso;
                $t->title = $row->title;
                $t->description = $row->description;
                $t->speakerTitle = $row->speaker_title;
                $entry->translation = $t;
            }

            // Category
            if ($entry->categoryId !== null && $row->cat_slug !== null) {
                $cat = new ProgramCategory();
                $cat->id = $entry->categoryId;
                $cat->slug = $row->cat_slug;
                $cat->color = $row->cat_color ?? '#3B82F6';
                $cat->icon = $row->cat_icon;
                if ($row->cat_name !== null) {
                    $ct = new ProgramCategoryTranslation();
                    $ct->categoryId = $cat->id;
                    $ct->languageIso = $languageIso;
                    $ct->name = $row->cat_name;
                    $cat->translation = $ct;
                }
                $entry->category = $cat;
            }

            $entries[] = $entry;
        }

        return $entries;
    }
}
