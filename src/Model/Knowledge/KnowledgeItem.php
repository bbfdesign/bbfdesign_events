<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Model\Knowledge;

class KnowledgeItem
{
    public int $id = 0;
    public string $slug = '';
    public ?string $image = null;
    public ?string $icon = null;
    public bool $isActive = true;
    public int $sortOrder = 0;

    /** @var KnowledgeItemTranslation[] */
    public array $translations = [];

    public ?KnowledgeItemTranslation $translation = null;

    /** @var KnowledgeCategory[] */
    public array $categories = [];

    public function getTitle(): string
    {
        return $this->translation?->title ?? '';
    }

    public function getTeaser(): string
    {
        return $this->translation?->teaser ?? '';
    }

    public function getContent(): string
    {
        return $this->translation?->content ?? '';
    }
}
