<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Service;

use JTL\DB\DbInterface;
use Plugin\bbfdesign_events\src\Enum\TicketSourceType;
use Plugin\bbfdesign_events\src\Helper\DateHelper;
use Plugin\bbfdesign_events\src\Model\Ticket\TicketCategory;
use Plugin\bbfdesign_events\src\Model\Ticket\TicketCategoryTranslation;
use Plugin\bbfdesign_events\src\Model\Ticket\TicketOption;
use Plugin\bbfdesign_events\src\Model\Ticket\TicketOptionTranslation;

class TicketService
{
    public function __construct(
        private readonly DbInterface $db
    ) {}

    /**
     * @return TicketOption[]
     */
    public function getTicketsForEvent(int $eventId, string $languageIso): array
    {
        $rows = $this->db->getObjects(
            'SELECT t.*, tt.name, tt.description as ticket_desc, tt.cta_label, tt.hint,
                    tc.slug as tcat_slug, tc.color as tcat_color, tc.icon as tcat_icon,
                    tct.name as tcat_name, tct.description as tcat_desc
             FROM bbf_event_tickets t
             LEFT JOIN bbf_event_tickets_translation tt ON t.id = tt.ticket_id AND tt.language_iso = :lang
             LEFT JOIN bbf_ticket_categories tc ON t.category_id = tc.id
             LEFT JOIN bbf_ticket_categories_translation tct ON tc.id = tct.category_id AND tct.language_iso = :lang
             WHERE t.event_id = :eid AND t.is_active = 1
             ORDER BY t.sort_order',
            ['eid' => $eventId, 'lang' => $languageIso]
        );

        $tickets = [];
        foreach ($rows as $row) {
            $ticket = $this->hydrateTicket($row, $languageIso);
            $this->resolveWawiData($ticket);
            $tickets[] = $ticket;
        }

        return $tickets;
    }

    public function hasTickets(int $eventId): bool
    {
        $row = $this->db->getSingleObject(
            'SELECT COUNT(*) as cnt FROM bbf_event_tickets WHERE event_id = :eid AND is_active = 1',
            ['eid' => $eventId]
        );
        return (int) ($row->cnt ?? 0) > 0;
    }

    public function isFullySoldOut(int $eventId): bool
    {
        $row = $this->db->getSingleObject(
            'SELECT COUNT(*) as total,
                    SUM(CASE WHEN is_sold_out = 1 THEN 1 ELSE 0 END) as sold_out
             FROM bbf_event_tickets
             WHERE event_id = :eid AND is_active = 1',
            ['eid' => $eventId]
        );

        $total = (int) ($row->total ?? 0);
        if ($total === 0) {
            return false;
        }

        return (int) ($row->sold_out ?? 0) >= $total;
    }

    private function hydrateTicket(object $row, string $languageIso): TicketOption
    {
        $ticket = new TicketOption();
        $ticket->id = (int) $row->id;
        $ticket->eventId = (int) $row->event_id;
        $ticket->categoryId = $row->category_id !== null ? (int) $row->category_id : null;
        $ticket->sourceType = TicketSourceType::from($row->source_type);
        $ticket->wawiArticleId = $row->wawi_article_id !== null ? (int) $row->wawi_article_id : null;
        $ticket->wawiArticleNo = $row->wawi_article_no;
        $ticket->externalUrl = $row->external_url;
        $ticket->externalProvider = $row->external_provider;
        $ticket->priceNet = $row->price_net !== null ? (float) $row->price_net : null;
        $ticket->priceGross = $row->price_gross !== null ? (float) $row->price_gross : null;
        $ticket->taxRate = $row->tax_rate !== null ? (float) $row->tax_rate : null;
        $ticket->currency = $row->currency ?? 'EUR';
        $ticket->maxQuantity = $row->max_quantity !== null ? (int) $row->max_quantity : null;
        $ticket->soldQuantity = (int) ($row->sold_quantity ?? 0);
        $ticket->availableFrom = DateHelper::parseDateTime($row->available_from);
        $ticket->availableTo = DateHelper::parseDateTime($row->available_to);
        $ticket->isActive = (bool) $row->is_active;
        $ticket->isSoldOut = (bool) $row->is_sold_out;
        $ticket->sortOrder = (int) $row->sort_order;

        if ($row->name !== null) {
            $t = new TicketOptionTranslation();
            $t->ticketId = $ticket->id;
            $t->languageIso = $languageIso;
            $t->name = $row->name;
            $t->description = $row->ticket_desc;
            $t->ctaLabel = $row->cta_label;
            $t->hint = $row->hint;
            $ticket->translation = $t;
        }

        if ($ticket->categoryId !== null && isset($row->tcat_slug)) {
            $cat = new TicketCategory();
            $cat->id = $ticket->categoryId;
            $cat->slug = $row->tcat_slug;
            $cat->color = $row->tcat_color ?? '#3B82F6';
            $cat->icon = $row->tcat_icon;
            if ($row->tcat_name !== null) {
                $ct = new TicketCategoryTranslation();
                $ct->categoryId = $cat->id;
                $ct->languageIso = $languageIso;
                $ct->name = $row->tcat_name;
                $ct->description = $row->tcat_desc;
                $cat->translation = $ct;
            }
            $ticket->category = $cat;
        }

        return $ticket;
    }

    private function resolveWawiData(TicketOption $ticket): void
    {
        if ($ticket->sourceType !== TicketSourceType::WAWI_ARTICLE || $ticket->wawiArticleId === null) {
            return;
        }

        try {
            $artikel = new \JTL\Catalog\Product\Artikel();
            $artikel->fuelleArtikel($ticket->wawiArticleId);

            if ($artikel->kArtikel > 0) {
                $ticket->resolvedPrice = $artikel->Preise->fVKBrutto ?? $ticket->priceGross;
                $ticket->resolvedAvailable = ($artikel->fLagerbestand > 0 || $artikel->cLagerBewortet === 'N');
                $ticket->wawiArticle = $artikel;
                $ticket->addToCartUrl = '/?a=' . $artikel->kArtikel . '&wk=1';
            }
        } catch (\Throwable) {
            // Wawi article not found or not accessible
        }
    }
}
