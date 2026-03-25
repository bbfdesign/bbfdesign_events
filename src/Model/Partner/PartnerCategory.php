<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Model\Partner;

class PartnerCategory
{
    public int $id = 0;
    public string $slug = '';
    public int $sortOrder = 0;

    /** @var PartnerCategoryTranslation[] */
    public array $translations = [];

    public ?PartnerCategoryTranslation $translation = null;

    public function getName(): string
    {
        return $this->translation?->name ?? '';
    }
}
