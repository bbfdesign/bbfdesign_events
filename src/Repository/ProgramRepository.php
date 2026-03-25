<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Repository;

use JTL\DB\DbInterface;
use Plugin\bbfdesign_events\src\Helper\DateHelper;

class ProgramRepository
{
    public function __construct(private readonly DbInterface $db) {}

    public function saveEntry(int $eventId, array $data): int
    {
        $entryData = (object) [
            'event_id' => $eventId,
            'event_date_id' => ($data['event_date_id'] ?? '') !== '' ? (int) $data['event_date_id'] : null,
            'category_id' => ($data['category_id'] ?? '') !== '' ? (int) $data['category_id'] : null,
            'time_start' => ($data['time_start'] ?? '') !== '' ? $data['time_start'] : null,
            'time_end' => ($data['time_end'] ?? '') !== '' ? $data['time_end'] : null,
            'speaker_name' => ($data['speaker_name'] ?? '') !== '' ? $data['speaker_name'] : null,
            'speaker_image' => ($data['speaker_image'] ?? '') !== '' ? $data['speaker_image'] : null,
            'link_url' => ($data['link_url'] ?? '') !== '' ? $data['link_url'] : null,
            'link_target' => $data['link_target'] ?? '_self',
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_highlight' => isset($data['is_highlight']) ? 1 : 0,
        ];

        $entryId = (int) ($data['id'] ?? 0);
        if ($entryId > 0) {
            $this->db->update('bbf_event_program_entries', 'id', $entryId, $entryData);
        } else {
            $entryId = $this->db->insert('bbf_event_program_entries', $entryData);
        }

        return $entryId;
    }

    public function saveEntryTranslation(int $entryId, string $languageIso, array $data): void
    {
        $tData = (object) [
            'entry_id' => $entryId,
            'language_iso' => $languageIso,
            'title' => $data['title'] ?? '',
            'description' => ($data['description'] ?? '') !== '' ? $data['description'] : null,
            'speaker_title' => ($data['speaker_title'] ?? '') !== '' ? $data['speaker_title'] : null,
        ];

        $existing = $this->db->getSingleObject(
            'SELECT id FROM bbf_event_program_entries_translation WHERE entry_id = :eid AND language_iso = :lang',
            ['eid' => $entryId, 'lang' => $languageIso]
        );

        if ($existing) {
            $this->db->update('bbf_event_program_entries_translation', 'id', (int) $existing->id, $tData);
        } else {
            $this->db->insert('bbf_event_program_entries_translation', $tData);
        }
    }

    public function deleteEntry(int $id): bool
    {
        return $this->db->delete('bbf_event_program_entries', 'id', $id) > 0;
    }

    public function deleteAllForEvent(int $eventId): void
    {
        $this->db->executeQuery(
            'DELETE FROM bbf_event_program_entries WHERE event_id = :eid',
            ['eid' => $eventId]
        );
    }
}
