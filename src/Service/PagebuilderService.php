<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Service;

use Plugin\bbfdesign_events\src\Model\Event;
use Plugin\bbfdesign_events\src\Model\Pagebuilder\EventPage;
use Plugin\bbfdesign_events\src\Repository\PagebuilderRepository;

class PagebuilderService
{
    public function __construct(
        private readonly PagebuilderRepository $repository,
        private readonly ProgramService $programService,
        private readonly PartnerService $partnerService,
        private readonly TicketService $ticketService,
        private readonly KnowledgeService $knowledgeService,
        private readonly AreaService $areaService
    ) {}

    public function renderPage(Event $event, string $languageIso): PageRenderResult
    {
        $page = $this->repository->findByEventAndLanguage($event->id, $languageIso);

        if ($page === null || !$page->hasContent()) {
            return new PageRenderResult('', '');
        }

        $html = $this->replaceDynamicBlocks($page->htmlRendered, $event, $languageIso);

        return new PageRenderResult($html, $page->cssRendered ?? '');
    }

    public function loadPageData(int $eventId, string $languageIso): ?EventPage
    {
        return $this->repository->findByEventAndLanguage($eventId, $languageIso);
    }

    public function savePage(EventPage $page): int
    {
        return $this->repository->savePage($page);
    }

    private function replaceDynamicBlocks(string $html, Event $event, string $lang): string
    {
        return preg_replace_callback(
            '/<div[^>]*data-bbf-dynamic="(\w+)"[^>]*>.*?<\/div>/s',
            function (array $matches) use ($event, $lang) {
                $blockType = $matches[1];
                $traits = $this->extractTraits($matches[0]);
                return $this->renderDynamicBlock($blockType, $event, $lang, $traits);
            },
            $html
        ) ?? $html;
    }

    private function renderDynamicBlock(string $type, Event $event, string $lang, array $traits): string
    {
        return match ($type) {
            'program' => $this->renderProgramBlock($event, $lang, $traits),
            'partners' => $this->renderPartnersBlock($event, $lang, $traits),
            'tickets' => $this->renderTicketsBlock($event, $lang, $traits),
            'knowledge' => $this->renderKnowledgeBlock($event, $lang, $traits),
            'area_map' => $this->renderAreaBlock($event, $lang, $traits),
            'teaser_list' => $this->renderTeaserBlock($event, $lang, $traits),
            default => '',
        };
    }

    private function renderProgramBlock(Event $event, string $lang, array $traits): string
    {
        $entries = $this->programService->getEntriesForEvent($event->id, $lang);
        if (empty($entries)) {
            return '';
        }

        $mode = $traits['display-mode'] ?? 'timeline';
        // Delegate to Smarty template rendering
        return $this->renderSmartyBlock('blocks/program.tpl', [
            'programEntries' => $entries,
            'displayMode' => $mode,
            'groupByDay' => ($traits['group-by-day'] ?? 'true') === 'true',
            'showSpeakers' => ($traits['show-speakers'] ?? 'true') === 'true',
            'showCategories' => ($traits['show-categories'] ?? 'true') === 'true',
        ]);
    }

    private function renderPartnersBlock(Event $event, string $lang, array $traits): string
    {
        $partners = $this->partnerService->getPartnersForEvent($event->id, $lang);
        if (empty($partners)) {
            return '';
        }

        return $this->renderSmartyBlock('blocks/partners.tpl', [
            'partners' => $partners,
            'displayMode' => $traits['display-mode'] ?? 'logo_grid',
            'columns' => (int) ($traits['columns'] ?? 4),
            'showDescription' => ($traits['show-description'] ?? 'false') === 'true',
            'enableModal' => ($traits['enable-modal'] ?? 'true') === 'true',
        ]);
    }

    private function renderTicketsBlock(Event $event, string $lang, array $traits): string
    {
        $tickets = $this->ticketService->getTicketsForEvent($event->id, $lang);
        if (empty($tickets)) {
            return '';
        }

        return $this->renderSmartyBlock('blocks/tickets.tpl', [
            'tickets' => $tickets,
            'displayMode' => $traits['display-mode'] ?? 'cards',
            'showPrice' => ($traits['show-price'] ?? 'true') === 'true',
            'showAvailability' => ($traits['show-availability'] ?? 'true') === 'true',
            'showDescription' => ($traits['show-description'] ?? 'true') === 'true',
        ]);
    }

    private function renderKnowledgeBlock(Event $event, string $lang, array $traits): string
    {
        $items = $this->knowledgeService->getItemsForEvent($event->id, $lang);
        if (empty($items)) {
            return '';
        }

        return $this->renderSmartyBlock('blocks/knowledge.tpl', [
            'knowledgeItems' => $items,
            'displayMode' => $traits['display-mode'] ?? 'cards',
            'columns' => (int) ($traits['columns'] ?? 3),
            'showImage' => ($traits['show-image'] ?? 'true') === 'true',
            'showCta' => ($traits['show-cta'] ?? 'true') === 'true',
        ]);
    }

    private function renderAreaBlock(Event $event, string $lang, array $traits): string
    {
        $maps = $this->areaService->getMapsForEvent($event->id, $lang);
        if (empty($maps)) {
            return '';
        }

        return $this->renderSmartyBlock('blocks/area_map.tpl', [
            'areaMaps' => $maps,
            'mapHeight' => $traits['map-height'] ?? '400px',
            'showGroupFilter' => ($traits['show-group-filter'] ?? 'true') === 'true',
            'showMarkerList' => ($traits['show-marker-list'] ?? 'true') === 'true',
        ]);
    }

    private function renderTeaserBlock(Event $event, string $lang, array $traits): string
    {
        // Placeholder: will be implemented with EventService dependency
        return $this->renderSmartyBlock('blocks/teaser_list.tpl', [
            'limit' => (int) ($traits['limit'] ?? 3),
            'source' => $traits['source'] ?? 'upcoming',
            'currentEventId' => $event->id,
        ]);
    }

    private function extractTraits(string $html): array
    {
        $traits = [];
        if (preg_match_all('/data-trait-([a-z-]+)="([^"]*)"/', $html, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $traits[$match[1]] = $match[2];
            }
        }
        return $traits;
    }

    private function renderSmartyBlock(string $template, array $data): string
    {
        // Integration with JTL Smarty will be done via Shop::Smarty()
        // For now, return a placeholder that the frontend controller will replace
        $smarty = \JTL\Shop::Smarty();
        $pluginPath = \PFAD_ROOT . 'plugins/bbfdesign_events/frontend/template/events/';

        foreach ($data as $key => $value) {
            $smarty->assign($key, $value);
        }

        $smarty->assign('bbfEventsPath', $pluginPath);

        return $smarty->fetch($pluginPath . $template);
    }
}
