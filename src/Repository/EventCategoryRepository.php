<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Repository;

use JTL\DB\DbInterface;
use Plugin\bbfdesign_events\src\Model\EventCategory;
use Plugin\bbfdesign_events\src\Model\EventCategoryTranslation;

class EventCategoryRepository
{
    public function __construct(
        private readonly DbInterface $db
    ) {}

    public function findById(int $id): ?EventCategory
    {
        $row = $this->db->getSingleObject(
            'SELECT * FROM bbf_event_categories WHERE id = :id',
            ['id' => $id]
        );

        if ($row === null) {
            return null;
        }

        $category = $this->hydrateCategory($row);
        $this->loadTranslations($category);

        return $category;
    }

    public function findBySlug(string $slug): ?EventCategory
    {
        $row = $this->db->getSingleObject(
            'SELECT * FROM bbf_event_categories WHERE slug = :slug',
            ['slug' => $slug]
        );

        if ($row === null) {
            return null;
        }

        $category = $this->hydrateCategory($row);
        $this->loadTranslations($category);

        return $category;
    }

    /**
     * @return EventCategory[]
     */
    public function findAll(bool $activeOnly = true): array
    {
        $where = $activeOnly ? 'WHERE is_active = 1' : '';
        $rows = $this->db->getObjects(
            "SELECT * FROM bbf_event_categories {$where} ORDER BY sort_order, id"
        );

        $categories = [];
        foreach ($rows as $row) {
            $cat = $this->hydrateCategory($row);
            $this->loadTranslations($cat);
            $categories[] = $cat;
        }

        return $categories;
    }

    /**
     * @return EventCategory[]
     */
    public function findTree(bool $activeOnly = true): array
    {
        $all = $this->findAll($activeOnly);
        return $this->buildTree($all);
    }

    public function save(EventCategory $category): int
    {
        $data = (object) [
            'slug' => $category->slug,
            'parent_id' => $category->parentId,
            'sort_order' => $category->sortOrder,
            'is_active' => $category->isActive ? 1 : 0,
            'image' => $category->image,
        ];

        if ($category->id > 0) {
            $this->db->update('bbf_event_categories', 'id', $category->id, $data);
            return $category->id;
        }

        return $this->db->insert('bbf_event_categories', $data);
    }

    public function saveTranslation(EventCategoryTranslation $translation): int
    {
        $data = (object) [
            'category_id' => $translation->categoryId,
            'language_iso' => $translation->languageIso,
            'name' => $translation->name,
            'description' => $translation->description,
            'meta_title' => $translation->metaTitle,
            'meta_description' => $translation->metaDescription,
        ];

        if ($translation->id > 0) {
            $this->db->update('bbf_event_categories_translation', 'id', $translation->id, $data);
            return $translation->id;
        }

        return $this->db->insert('bbf_event_categories_translation', $data);
    }

    public function delete(int $id): bool
    {
        return $this->db->delete('bbf_event_categories', 'id', $id) > 0;
    }

    public function slugExists(string $slug, ?int $excludeId = null): bool
    {
        $params = ['slug' => $slug];
        $exclude = '';
        if ($excludeId !== null) {
            $exclude = ' AND id != :exclude_id';
            $params['exclude_id'] = $excludeId;
        }

        $row = $this->db->getSingleObject(
            "SELECT COUNT(*) as cnt FROM bbf_event_categories WHERE slug = :slug{$exclude}",
            $params
        );

        return (int) ($row->cnt ?? 0) > 0;
    }

    public function getEventCount(int $categoryId): int
    {
        $row = $this->db->getSingleObject(
            'SELECT COUNT(*) as cnt FROM bbf_event_category_mapping WHERE category_id = :cid',
            ['cid' => $categoryId]
        );

        return (int) ($row->cnt ?? 0);
    }

    private function hydrateCategory(object $row): EventCategory
    {
        $cat = new EventCategory();
        $cat->id = (int) $row->id;
        $cat->slug = $row->slug;
        $cat->parentId = $row->parent_id !== null ? (int) $row->parent_id : null;
        $cat->sortOrder = (int) $row->sort_order;
        $cat->isActive = (bool) $row->is_active;
        $cat->image = $row->image;

        return $cat;
    }

    private function loadTranslations(EventCategory $category): void
    {
        $rows = $this->db->getObjects(
            'SELECT * FROM bbf_event_categories_translation WHERE category_id = :cid',
            ['cid' => $category->id]
        );

        foreach ($rows as $row) {
            $t = new EventCategoryTranslation();
            $t->id = (int) $row->id;
            $t->categoryId = (int) $row->category_id;
            $t->languageIso = $row->language_iso;
            $t->name = $row->name;
            $t->description = $row->description;
            $t->metaTitle = $row->meta_title;
            $t->metaDescription = $row->meta_description;
            $category->translations[] = $t;
        }
    }

    /**
     * @param EventCategory[] $categories
     * @return EventCategory[]
     */
    private function buildTree(array $categories, ?int $parentId = null): array
    {
        $tree = [];
        foreach ($categories as $cat) {
            if ($cat->parentId === $parentId) {
                $cat->children = $this->buildTree($categories, $cat->id);
                $tree[] = $cat;
            }
        }
        return $tree;
    }
}
