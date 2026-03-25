<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Model;

class EventCategory
{
    public int $id = 0;
    public string $slug = '';
    public ?int $parentId = null;
    public int $sortOrder = 0;
    public bool $isActive = true;
    public ?string $image = null;

    /** @var EventCategoryTranslation[] */
    public array $translations = [];

    public ?EventCategoryTranslation $translation = null;

    /** @var EventCategory[] */
    public array $children = [];

    public function getName(): string
    {
        return $this->translation?->name ?? '';
    }

    public function getDescription(): string
    {
        return $this->translation?->description ?? '';
    }
}
