<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Api;

use JTL\Shop;
use Plugin\bbfdesign_events\src\Service\TicketService;

class TicketApiController
{
    private TicketService $ticketService;

    public function __construct()
    {
        $this->ticketService = new TicketService(Shop::Container()->getDB());
    }

    public function getByEvent(int $eventId, string $lang): array
    {
        $tickets = $this->ticketService->getTicketsForEvent($eventId, $lang);
        return array_map(fn($t) => [
            'id' => $t->id,
            'name' => $t->getName(),
            'description' => $t->getDescription(),
            'source_type' => $t->sourceType->value,
            'price' => $t->getDisplayPrice(),
            'currency' => $t->currency,
            'is_available' => $t->isAvailable(),
            'is_sold_out' => $t->isSoldOut,
            'external_url' => $t->externalUrl,
            'add_to_cart_url' => $t->addToCartUrl,
            'cta_label' => $t->getCtaLabel(),
            'category' => $t->category ? ['id' => $t->category->id, 'name' => $t->category->getName(), 'color' => $t->category->color] : null,
        ], $tickets);
    }

    public function hasTickets(int $eventId): bool
    {
        return $this->ticketService->hasTickets($eventId);
    }

    public function isSoldOut(int $eventId): bool
    {
        return $this->ticketService->isFullySoldOut($eventId);
    }
}
