<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Model\Ticket;

class TicketOptionTranslation
{
    public int $id = 0;
    public int $ticketId = 0;
    public string $languageIso = '';
    public string $name = '';
    public ?string $description = null;
    public ?string $ctaLabel = null;
    public ?string $hint = null;
}
