<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Model\Partner;

class EventPartner
{
    public int $id = 0;
    public int $eventId = 0;
    public int $partnerId = 0;
    public ?int $categoryId = null;
    public int $sortOrder = 0;
    public bool $isVisible = true;

    public ?Partner $partner = null;
    public ?PartnerCategory $category = null;
}
