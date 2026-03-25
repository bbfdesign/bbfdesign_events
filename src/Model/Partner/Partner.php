<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Model\Partner;

class Partner
{
    public int $id = 0;
    public string $slug = '';
    public ?string $logo = null;
    public ?string $logoDark = null;
    public ?string $websiteUrl = null;
    public bool $isActive = true;
    public int $sortOrder = 0;

    /** @var PartnerTranslation[] */
    public array $translations = [];

    public ?PartnerTranslation $translation = null;

    /** @var PartnerCategory[] */
    public array $categories = [];

    public function getName(): string
    {
        return $this->translation?->name ?? '';
    }

    public function getShortDesc(): string
    {
        return $this->translation?->shortDesc ?? '';
    }

    public function getLongDesc(): string
    {
        return $this->translation?->longDesc ?? '';
    }
}
