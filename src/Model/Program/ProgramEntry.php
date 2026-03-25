<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Model\Program;

class ProgramEntry
{
    public int $id = 0;
    public int $eventId = 0;
    public ?int $eventDateId = null;
    public ?int $categoryId = null;
    public ?\DateTimeImmutable $timeStart = null;
    public ?\DateTimeImmutable $timeEnd = null;
    public ?string $speakerName = null;
    public ?string $speakerImage = null;
    public ?string $linkUrl = null;
    public string $linkTarget = '_self';
    public int $sortOrder = 0;
    public bool $isHighlight = false;

    public ?ProgramCategory $category = null;

    /** @var ProgramEntryTranslation[] */
    public array $translations = [];

    public ?ProgramEntryTranslation $translation = null;

    public function getTitle(): string
    {
        return $this->translation?->title ?? '';
    }

    public function getDescription(): string
    {
        return $this->translation?->description ?? '';
    }

    public function getSpeakerTitle(): string
    {
        return $this->translation?->speakerTitle ?? '';
    }

    public function getTimeRange(): string
    {
        if ($this->timeStart === null) {
            return '';
        }
        $start = $this->timeStart->format('H:i');
        if ($this->timeEnd !== null) {
            return $start . ' – ' . $this->timeEnd->format('H:i');
        }
        return $start . ' Uhr';
    }
}
