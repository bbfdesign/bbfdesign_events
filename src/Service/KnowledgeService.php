<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Service;

use JTL\DB\DbInterface;
use Plugin\bbfdesign_events\src\Model\Knowledge\KnowledgeItem;
use Plugin\bbfdesign_events\src\Model\Knowledge\KnowledgeItemTranslation;

class KnowledgeService
{
    public function __construct(
        private readonly DbInterface $db
    ) {}

    /**
     * @return KnowledgeItem[]
     */
    public function getItemsForEvent(int $eventId, string $languageIso): array
    {
        $rows = $this->db->getObjects(
            'SELECT ki.*, kit.title, kit.teaser, kit.content, kit.cta_label, kit.cta_url,
                    ekm.sort_order as event_sort
             FROM bbf_event_knowledge_mapping ekm
             JOIN bbf_knowledge_items ki ON ekm.item_id = ki.id
             LEFT JOIN bbf_knowledge_items_translation kit ON ki.id = kit.item_id AND kit.language_iso = :lang
             WHERE ekm.event_id = :eid AND ki.is_active = 1
             ORDER BY ekm.sort_order, ki.sort_order',
            ['eid' => $eventId, 'lang' => $languageIso]
        );

        $items = [];
        foreach ($rows as $row) {
            $item = new KnowledgeItem();
            $item->id = (int) $row->id;
            $item->slug = $row->slug;
            $item->image = $row->image;
            $item->icon = $row->icon;
            $item->isActive = (bool) $row->is_active;
            $item->sortOrder = (int) $row->event_sort;

            if ($row->title !== null) {
                $t = new KnowledgeItemTranslation();
                $t->itemId = $item->id;
                $t->languageIso = $languageIso;
                $t->title = $row->title;
                $t->teaser = $row->teaser;
                $t->content = $row->content;
                $t->ctaLabel = $row->cta_label;
                $t->ctaUrl = $row->cta_url;
                $item->translation = $t;
            }

            $items[] = $item;
        }

        return $items;
    }

    /**
     * @return KnowledgeItem[]
     */
    public function getAllItems(string $languageIso, bool $activeOnly = true): array
    {
        $where = $activeOnly ? 'WHERE ki.is_active = 1' : '';
        $rows = $this->db->getObjects(
            "SELECT ki.*, kit.title, kit.teaser, kit.content, kit.cta_label, kit.cta_url
             FROM bbf_knowledge_items ki
             LEFT JOIN bbf_knowledge_items_translation kit ON ki.id = kit.item_id AND kit.language_iso = :lang
             {$where}
             ORDER BY ki.sort_order, ki.id",
            ['lang' => $languageIso]
        );

        $items = [];
        foreach ($rows as $row) {
            $item = new KnowledgeItem();
            $item->id = (int) $row->id;
            $item->slug = $row->slug;
            $item->image = $row->image;
            $item->icon = $row->icon;
            $item->isActive = (bool) $row->is_active;
            $item->sortOrder = (int) $row->sort_order;

            if ($row->title !== null) {
                $t = new KnowledgeItemTranslation();
                $t->itemId = $item->id;
                $t->languageIso = $languageIso;
                $t->title = $row->title;
                $t->teaser = $row->teaser;
                $t->content = $row->content;
                $t->ctaLabel = $row->cta_label;
                $t->ctaUrl = $row->cta_url;
                $item->translation = $t;
            }

            $items[] = $item;
        }

        return $items;
    }
}
