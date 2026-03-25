<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Repository;

use JTL\DB\DbInterface;

class KnowledgeRepository
{
    public function __construct(private readonly DbInterface $db) {}

    public function findById(int $id): ?object
    {
        return $this->db->getSingleObject('SELECT * FROM bbf_knowledge_items WHERE id = :id', ['id' => $id]);
    }

    public function findAll(bool $activeOnly = true): array
    {
        $where = $activeOnly ? 'WHERE is_active = 1' : '';
        return $this->db->getObjects("SELECT * FROM bbf_knowledge_items {$where} ORDER BY sort_order, id");
    }

    public function syncEventKnowledge(int $eventId, array $itemIds): void
    {
        $this->db->executeQuery(
            'DELETE FROM bbf_event_knowledge_mapping WHERE event_id = :eid',
            ['eid' => $eventId]
        );

        foreach ($itemIds as $i => $itemId) {
            $this->db->insert('bbf_event_knowledge_mapping', (object) [
                'event_id' => $eventId,
                'item_id' => (int) $itemId,
                'sort_order' => $i,
            ]);
        }
    }
}
