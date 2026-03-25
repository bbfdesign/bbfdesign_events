<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Model;

class EventTimeSlot
{
    public int $id = 0;
    public int $eventDateId = 0;
    public \DateTimeImmutable $timeStart;
    public ?\DateTimeImmutable $timeEnd = null;
    public ?string $label = null;
    public int $sortOrder = 0;

    public function __construct()
    {
        $this->timeStart = new \DateTimeImmutable();
    }

    public function getFormattedRange(): string
    {
        $start = $this->timeStart->format('H:i');
        if ($this->timeEnd !== null) {
            return $start . ' – ' . $this->timeEnd->format('H:i');
        }
        return $start . ' Uhr';
    }
}
