<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Repository;

use JTL\DB\DbInterface;

class TicketRepository
{
    public function __construct(private readonly DbInterface $db) {}

    public function findByEvent(int $eventId, string $languageIso): array
    {
        return $this->db->getObjects(
            'SELECT t.*, tt.name, tt.description as ticket_desc, tt.cta_label, tt.hint
             FROM bbf_event_tickets t
             LEFT JOIN bbf_event_tickets_translation tt ON t.id = tt.ticket_id AND tt.language_iso = :lang
             WHERE t.event_id = :eid AND t.is_active = 1
             ORDER BY t.sort_order',
            ['eid' => $eventId, 'lang' => $languageIso]
        );
    }

    public function findActiveByEvent(int $eventId): array
    {
        return $this->db->getObjects(
            'SELECT * FROM bbf_event_tickets WHERE event_id = :eid AND is_active = 1 ORDER BY sort_order',
            ['eid' => $eventId]
        );
    }

    public function countByEvent(int $eventId): int
    {
        $row = $this->db->getSingleObject(
            'SELECT COUNT(*) as cnt FROM bbf_event_tickets WHERE event_id = :eid AND is_active = 1',
            ['eid' => $eventId]
        );
        return (int) ($row->cnt ?? 0);
    }

    public function saveTicket(int $eventId, array $data): int
    {
        $ticketData = (object) [
            'event_id' => $eventId,
            'category_id' => ($data['category_id'] ?? '') !== '' ? (int) $data['category_id'] : null,
            'source_type' => $data['source_type'] ?? 'external',
            'wawi_article_id' => ($data['wawi_article_id'] ?? '') !== '' ? (int) $data['wawi_article_id'] : null,
            'wawi_article_no' => ($data['wawi_article_no'] ?? '') !== '' ? $data['wawi_article_no'] : null,
            'external_url' => ($data['external_url'] ?? '') !== '' ? $data['external_url'] : null,
            'external_provider' => ($data['external_provider'] ?? '') !== '' ? $data['external_provider'] : null,
            'price_net' => ($data['price_net'] ?? '') !== '' ? (float) $data['price_net'] : null,
            'price_gross' => ($data['price_gross'] ?? '') !== '' ? (float) $data['price_gross'] : null,
            'tax_rate' => ($data['tax_rate'] ?? '') !== '' ? (float) $data['tax_rate'] : null,
            'currency' => $data['currency'] ?? 'EUR',
            'max_quantity' => ($data['max_quantity'] ?? '') !== '' ? (int) $data['max_quantity'] : null,
            'available_from' => ($data['available_from'] ?? '') !== '' ? $data['available_from'] : null,
            'available_to' => ($data['available_to'] ?? '') !== '' ? $data['available_to'] : null,
            'is_active' => isset($data['is_active']) ? 1 : 0,
            'is_sold_out' => isset($data['is_sold_out']) ? 1 : 0,
            'sort_order' => (int) ($data['sort_order'] ?? 0),
        ];

        $ticketId = (int) ($data['id'] ?? 0);
        if ($ticketId > 0) {
            $this->db->update('bbf_event_tickets', 'id', $ticketId, $ticketData);
        } else {
            $ticketId = $this->db->insert('bbf_event_tickets', $ticketData);
        }

        return $ticketId;
    }

    public function deleteTicket(int $id): bool
    {
        return $this->db->delete('bbf_event_tickets', 'id', $id) > 0;
    }

    public function deleteAllForEvent(int $eventId): void
    {
        $this->db->executeQuery(
            'DELETE FROM bbf_event_tickets WHERE event_id = :eid',
            ['eid' => $eventId]
        );
    }
}
