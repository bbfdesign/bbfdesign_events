<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Repository;

use JTL\DB\DbInterface;

class AreaRepository
{
    public function __construct(private readonly DbInterface $db) {}

    public function findById(int $id): ?object
    {
        return $this->db->getSingleObject('SELECT * FROM bbf_area_maps WHERE id = :id', ['id' => $id]);
    }

    public function findAll(bool $activeOnly = true): array
    {
        $where = $activeOnly ? 'WHERE is_active = 1' : '';
        return $this->db->getObjects("SELECT * FROM bbf_area_maps {$where} ORDER BY slug");
    }

    public function syncEventAreas(int $eventId, array $mapIds): void
    {
        $this->db->executeQuery(
            'DELETE FROM bbf_event_area_mapping WHERE event_id = :eid',
            ['eid' => $eventId]
        );

        foreach ($mapIds as $i => $mapId) {
            $this->db->insert('bbf_event_area_mapping', (object) [
                'event_id' => $eventId,
                'map_id' => (int) $mapId,
                'sort_order' => $i,
            ]);
        }
    }
}
