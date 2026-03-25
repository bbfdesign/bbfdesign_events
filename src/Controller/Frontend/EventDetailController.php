<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Controller\Frontend;

use JTL\Shop;
use Plugin\bbfdesign_events\src\Config\EventConfig;
use Plugin\bbfdesign_events\src\Helper\SchemaOrgHelper;
use Plugin\bbfdesign_events\src\Repository\PagebuilderRepository;
use Plugin\bbfdesign_events\src\Service\AreaService;
use Plugin\bbfdesign_events\src\Service\CacheService;
use Plugin\bbfdesign_events\src\Service\EventDateService;
use Plugin\bbfdesign_events\src\Service\EventService;
use Plugin\bbfdesign_events\src\Service\KnowledgeService;
use Plugin\bbfdesign_events\src\Service\PagebuilderService;
use Plugin\bbfdesign_events\src\Service\PartnerService;
use Plugin\bbfdesign_events\src\Service\ProgramService;
use Plugin\bbfdesign_events\src\Service\SeoService;
use Plugin\bbfdesign_events\src\Service\TicketService;

class EventDetailController
{
    private EventService $eventService;
    private SeoService $seoService;
    private PagebuilderService $pagebuilderService;
    private TicketService $ticketService;

    public function __construct()
    {
        $db = Shop::Container()->getDB();
        $cache = Shop::Container()->getCache();

        $this->seoService = new SeoService();
        $this->ticketService = new TicketService($db);

        $eventRepository = new \Plugin\bbfdesign_events\src\Repository\EventRepository($db);
        $dateService = new EventDateService();
        $cacheService = new CacheService($cache);

        $this->eventService = new EventService($eventRepository, $dateService, $this->seoService, $cacheService);

        $pagebuilderRepo = new PagebuilderRepository($db);
        $programService = new ProgramService($db);
        $partnerService = new PartnerService($db);
        $knowledgeService = new KnowledgeService($db);
        $areaService = new AreaService($db);

        $this->pagebuilderService = new PagebuilderService(
            $pagebuilderRepo,
            $programService,
            $partnerService,
            $this->ticketService,
            $knowledgeService,
            $areaService
        );
    }

    public function dispatch(array $route): void
    {
        $slug = $route['slug'] ?? '';
        $languageIso = Shop::getLanguageISO();
        $smarty = Shop::Smarty();

        $event = $this->eventService->getEventBySlug($slug, $languageIso);

        if ($event === null) {
            http_response_code(404);
            return;
        }

        // Pagebuilder output
        $pageResult = $this->pagebuilderService->renderPage($event, $languageIso);

        // Tickets
        $tickets = $this->ticketService->getTicketsForEvent($event->id, $languageIso);

        // Schema.org
        $schema = SchemaOrgHelper::generateEventSchema($event, Shop::getURL());
        $schema = SchemaOrgHelper::addTicketOffers($schema, $tickets, Shop::getURL());
        $schemaJsonLd = SchemaOrgHelper::toJsonLd($schema);

        $pluginTemplatePath = EventConfig::getPluginPath() . 'frontend/template/events/';

        $smarty->assign('event', $event);
        $smarty->assign('pageBuilderHtml', $pageResult->html);
        $smarty->assign('pageBuilderCss', $pageResult->css);
        $smarty->assign('tickets', $tickets);
        $smarty->assign('schemaJsonLd', $schemaJsonLd);
        $smarty->assign('bbfEventsPath', $pluginTemplatePath);

        // Meta
        $smarty->assign('meta_title', $this->seoService->getMetaTitle($event));
        $smarty->assign('meta_description', $this->seoService->getMetaDescription($event));
        $smarty->assign('canonical_url', Shop::getURL() . $this->seoService->getCanonicalUrl($event, $languageIso));

        // CSS
        $smarty->assign('bbfEventsCss', [
            '/' . EventConfig::getFrontendCssPath() . 'bbf-events.css',
            '/' . EventConfig::getFrontendCssPath() . 'bbf-events-detail.css',
        ]);

        $smarty->display($pluginTemplatePath . 'detail.tpl');
    }
}
