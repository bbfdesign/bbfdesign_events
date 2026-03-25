<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Model;

use Plugin\bbfdesign_events\src\Enum\LinkType;

class EventLink
{
    public int $id = 0;
    public int $eventId = 0;
    public LinkType $linkType = LinkType::EXTERNAL;
    public ?int $targetId = null;
    public ?string $targetUrl = null;
    public ?string $targetPlugin = null;
    public int $sortOrder = 0;
    public string $context = 'related';

    /** @var EventLinkTranslation[] */
    public array $translations = [];

    public ?EventLinkTranslation $translation = null;

    public function getLabel(): string
    {
        return $this->translation?->label ?? '';
    }

    public function getUrl(): string
    {
        return $this->targetUrl ?? '';
    }
}
