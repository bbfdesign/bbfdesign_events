<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Model\Area;

class EventArea
{
    public int $eventId = 0;
    public int $mapId = 0;
    public int $sortOrder = 0;

    public ?AreaMap $map = null;
}
