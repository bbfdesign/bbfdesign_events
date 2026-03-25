<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Model\Pagebuilder;

class EventPage
{
    public int $id = 0;
    public int $eventId = 0;
    public string $languageIso = '';
    public ?string $gjsData = null;
    public ?string $htmlRendered = null;
    public ?string $cssRendered = null;
    public \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function hasContent(): bool
    {
        return $this->htmlRendered !== null && $this->htmlRendered !== '';
    }
}
