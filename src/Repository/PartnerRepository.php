<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Repository;

use JTL\DB\DbInterface;

class PartnerRepository
{
    public function __construct(private readonly DbInterface $db) {}

    public function findById(int $id): ?object
    {
        return $this->db->getSingleObject('SELECT * FROM bbf_partners WHERE id = :id', ['id' => $id]);
    }

    public function findAll(bool $activeOnly = true): array
    {
        $where = $activeOnly ? 'WHERE is_active = 1' : '';
        return $this->db->getObjects("SELECT * FROM bbf_partners {$where} ORDER BY sort_order, id");
    }

    public function syncEventPartners(int $eventId, array $partnerMappings): void
    {
        $this->db->executeQuery(
            'DELETE FROM bbf_event_partner_mapping WHERE event_id = :eid',
            ['eid' => $eventId]
        );

        foreach ($partnerMappings as $i => $mapping) {
            $this->db->insert('bbf_event_partner_mapping', (object) [
                'event_id' => $eventId,
                'partner_id' => (int) $mapping['partner_id'],
                'category_id' => ($mapping['category_id'] ?? '') !== '' ? (int) $mapping['category_id'] : null,
                'sort_order' => $i,
                'is_visible' => isset($mapping['is_visible']) ? 1 : 1,
            ]);
        }
    }
}
