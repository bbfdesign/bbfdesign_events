<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Model\Program;

class ProgramCategory
{
    public int $id = 0;
    public string $slug = '';
    public string $color = '#3B82F6';
    public ?string $icon = null;
    public int $sortOrder = 0;

    /** @var ProgramCategoryTranslation[] */
    public array $translations = [];

    public ?ProgramCategoryTranslation $translation = null;

    public function getName(): string
    {
        return $this->translation?->name ?? '';
    }
}
