<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Model;

class EventTranslation
{
    public int $id = 0;
    public int $eventId = 0;
    public string $languageIso = '';
    public string $title = '';
    public ?string $subtitle = null;
    public ?string $teaser = null;
    public ?string $description = null;
    public ?string $slugLocalized = null;
    public ?string $metaTitle = null;
    public ?string $metaDescription = null;
    public ?string $ogTitle = null;
    public ?string $ogDescription = null;
    public ?string $ogImage = null;
}
