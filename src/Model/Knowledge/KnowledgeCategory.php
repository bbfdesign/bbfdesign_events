<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Model\Knowledge;

class KnowledgeCategory
{
    public int $id = 0;
    public string $slug = '';
    public int $sortOrder = 0;

    /** @var KnowledgeCategoryTranslation[] */
    public array $translations = [];

    public ?KnowledgeCategoryTranslation $translation = null;

    public function getName(): string
    {
        return $this->translation?->name ?? '';
    }
}
