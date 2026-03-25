<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Hook;

use JTL\Shop;
use Plugin\bbfdesign_events\src\Service\EventService;

class SearchHook
{
    public static function provideSearchData(array $args): void
    {
        $db = Shop::Container()->getDB();
        $languageIso = $args['languageIso'] ?? 'ger';

        $rows = $db->getObjects(
            "SELECT e.id, e.slug, e.hero_image,
                    et.title, et.teaser, et.description,
                    (SELECT MIN(ed.date_start) FROM bbf_event_dates ed WHERE ed.event_id = e.id) as next_date
             FROM bbf_events e
             LEFT JOIN bbf_events_translation et ON e.id = et.event_id AND et.language_iso = :lang
             WHERE e.status = 'published'
             ORDER BY next_date ASC",
            ['lang' => $languageIso]
        );

        foreach ($rows as $row) {
            $args['results'][] = [
                'type' => 'event',
                'id' => (int) $row->id,
                'title' => $row->title ?? '',
                'description' => $row->teaser ?? '',
                'content' => strip_tags($row->description ?? ''),
                'url' => '/veranstaltungen/' . $row->slug,
                'image' => $row->hero_image,
                'date' => $row->next_date,
            ];
        }
    }
}
