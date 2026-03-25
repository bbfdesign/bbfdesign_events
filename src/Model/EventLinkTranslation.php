<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Model;

class EventLinkTranslation
{
    public int $id = 0;
    public int $linkId = 0;
    public string $languageIso = '';
    public ?string $label = null;
    public ?string $description = null;
}
