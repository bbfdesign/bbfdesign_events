<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Model\Partner;

class PartnerTranslation
{
    public int $id = 0;
    public int $partnerId = 0;
    public string $languageIso = '';
    public string $name = '';
    public ?string $shortDesc = null;
    public ?string $longDesc = null;
    public ?string $ctaLabel = null;
    public ?string $ctaUrl = null;
}
