<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Model\Knowledge;

class EventKnowledge
{
    public int $id = 0;
    public int $eventId = 0;
    public int $itemId = 0;
    public int $sortOrder = 0;

    public ?KnowledgeItem $item = null;
}
