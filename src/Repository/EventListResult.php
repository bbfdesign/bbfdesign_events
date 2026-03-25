<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Repository;

use Plugin\bbfdesign_events\src\Model\Event;

class EventListResult
{
    /** @var Event[] */
    public readonly array $events;
    public readonly int $total;
    public readonly int $page;
    public readonly int $perPage;
    public readonly int $totalPages;

    /**
     * @param Event[] $events
     */
    public function __construct(array $events, int $total, int $page, int $perPage)
    {
        $this->events = $events;
        $this->total = $total;
        $this->page = $page;
        $this->perPage = $perPage;
        $this->totalPages = $perPage > 0 ? (int) ceil($total / $perPage) : 1;
    }

    public function hasNextPage(): bool
    {
        return $this->page < $this->totalPages;
    }

    public function hasPreviousPage(): bool
    {
        return $this->page > 1;
    }

    public function isEmpty(): bool
    {
        return empty($this->events);
    }
}
