<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Model\Ticket;

class TicketCategory
{
    public int $id = 0;
    public string $slug = '';
    public string $color = '#3B82F6';
    public ?string $icon = null;
    public int $sortOrder = 0;

    /** @var TicketCategoryTranslation[] */
    public array $translations = [];

    public ?TicketCategoryTranslation $translation = null;

    public function getName(): string
    {
        return $this->translation?->name ?? '';
    }

    public function getDescription(): string
    {
        return $this->translation?->description ?? '';
    }
}
