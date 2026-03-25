<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Model;

use Plugin\bbfdesign_events\src\Enum\EventStatus;
use Plugin\bbfdesign_events\src\Enum\EventDateType;

class Event
{
    public int $id = 0;
    public EventStatus $status = EventStatus::DRAFT;
    public string $slug = '';
    public ?string $heroImage = null;
    public EventDateType $eventType = EventDateType::SINGLE;
    public bool $isFeatured = false;
    public int $sortOrder = 0;
    public ?\DateTimeImmutable $publishFrom = null;
    public ?\DateTimeImmutable $publishTo = null;
    public \DateTimeImmutable $createdAt;
    public \DateTimeImmutable $updatedAt;
    public ?int $createdBy = null;

    /** @var EventTranslation[] */
    public array $translations = [];

    /** @var EventDate[] */
    public array $dates = [];

    /** @var EventCategory[] */
    public array $categories = [];

    /** @var EventMedia[] */
    public array $media = [];

    /** @var EventLink[] */
    public array $links = [];

    public ?EventTranslation $translation = null;

    public ?string $computedStatus = null;
    public ?\DateTimeImmutable $nextDate = null;
    public ?string $url = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function isPublished(): bool
    {
        return $this->status === EventStatus::PUBLISHED;
    }

    public function isVisible(): bool
    {
        if (!$this->isPublished()) {
            return false;
        }
        $now = new \DateTimeImmutable();
        if ($this->publishFrom !== null && $now < $this->publishFrom) {
            return false;
        }
        if ($this->publishTo !== null && $now > $this->publishTo) {
            return false;
        }
        return true;
    }

    public function getTitle(): string
    {
        return $this->translation?->title ?? '';
    }

    public function getSubtitle(): string
    {
        return $this->translation?->subtitle ?? '';
    }

    public function getTeaser(): string
    {
        return $this->translation?->teaser ?? '';
    }

    public function getDescription(): string
    {
        return $this->translation?->description ?? '';
    }
}
