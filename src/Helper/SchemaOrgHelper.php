<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Helper;

use Plugin\bbfdesign_events\src\Model\Event;
use Plugin\bbfdesign_events\src\Model\Ticket\TicketOption;

class SchemaOrgHelper
{
    public static function generateEventSchema(Event $event, string $baseUrl = ''): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Event',
            'name' => $event->getTitle(),
            'description' => $event->getTeaser(),
            'url' => $baseUrl . ($event->url ?? ''),
        ];

        if ($event->heroImage) {
            $schema['image'] = $baseUrl . $event->heroImage;
        }

        if (!empty($event->dates)) {
            $firstDate = $event->dates[0];
            $schema['startDate'] = $firstDate->dateStart->format('Y-m-d');

            if ($firstDate->dateEnd !== null) {
                $schema['endDate'] = $firstDate->dateEnd->format('Y-m-d');
            }

            if (!$firstDate->isAllday && !empty($firstDate->timeSlots)) {
                $firstSlot = $firstDate->timeSlots[0];
                $schema['startDate'] = $firstDate->dateStart->format('Y-m-d')
                    . 'T' . $firstSlot->timeStart->format('H:i:s');
            }
        }

        $schema['eventStatus'] = 'https://schema.org/EventScheduled';
        $schema['eventAttendanceMode'] = 'https://schema.org/OfflineEventAttendanceMode';

        return $schema;
    }

    public static function addTicketOffers(array $schema, array $tickets, string $baseUrl = ''): array
    {
        $offers = [];

        /** @var TicketOption $ticket */
        foreach ($tickets as $ticket) {
            if (!$ticket->isAvailable()) {
                continue;
            }

            $offer = [
                '@type' => 'Offer',
                'name' => $ticket->getName(),
                'availability' => $ticket->isSoldOut
                    ? 'https://schema.org/SoldOut'
                    : 'https://schema.org/InStock',
            ];

            $price = $ticket->getDisplayPrice();
            if ($price !== null) {
                $offer['price'] = number_format($price, 2, '.', '');
                $offer['priceCurrency'] = $ticket->currency;
            }

            if ($ticket->isExternal() && $ticket->externalUrl) {
                $offer['url'] = $ticket->externalUrl;
            } elseif ($ticket->addToCartUrl) {
                $offer['url'] = $baseUrl . $ticket->addToCartUrl;
            }

            if ($ticket->availableFrom !== null) {
                $offer['validFrom'] = $ticket->availableFrom->format('Y-m-d\TH:i:s');
            }

            $offers[] = $offer;
        }

        if (!empty($offers)) {
            $schema['offers'] = $offers;
        }

        return $schema;
    }

    public static function toJsonLd(array $schema): string
    {
        return '<script type="application/ld+json">'
            . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
            . '</script>';
    }
}
