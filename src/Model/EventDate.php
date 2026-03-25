<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Model;

class EventDate
{
    public int $id = 0;
    public int $eventId = 0;
    public \DateTimeImmutable $dateStart;
    public ?\DateTimeImmutable $dateEnd = null;
    public bool $isAllday = true;
    public int $sortOrder = 0;

    /** @var EventTimeSlot[] */
    public array $timeSlots = [];

    public function __construct()
    {
        $this->dateStart = new \DateTimeImmutable();
    }

    public function isSingleDay(): bool
    {
        return $this->dateEnd === null || $this->dateEnd->format('Y-m-d') === $this->dateStart->format('Y-m-d');
    }

    public function isMultiDay(): bool
    {
        return !$this->isSingleDay();
    }

    public function getDurationDays(): int
    {
        if ($this->dateEnd === null) {
            return 1;
        }
        return (int) $this->dateStart->diff($this->dateEnd)->days + 1;
    }
}
