<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Controller\Frontend;

use JTL\Plugin\Helper;
use JTL\Router\Controller\AbstractController;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Laminas\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Plugin\bbfdesign_events\src\Config\EventConfig;
use Plugin\bbfdesign_events\src\Helper\SchemaOrgHelper;
use Plugin\bbfdesign_events\src\Model\EventCategory;
use Plugin\bbfdesign_events\src\Repository\EventCategoryRepository;
use Plugin\bbfdesign_events\src\Repository\EventListFilter;
use Plugin\bbfdesign_events\src\Repository\EventRepository;
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

/**
 * Unified frontend controller for all event pages.
 * Extends JTL's AbstractController for proper router integration.
 */
class EventPageController extends AbstractController
{
    private EventService $eventService;
    private SeoService $seoService;
    private EventCategoryRepository $categoryRepository;
    private string $tplPath;

    private function initServices(): void
    {
        $cache = Shop::Container()->getCache();
        $this->seoService = new SeoService();
        $this->categoryRepository = new EventCategoryRepository($this->db);

        $eventRepository = new EventRepository($this->db);
        $dateService = new EventDateService();
        $cacheService = new CacheService($cache);
        $this->eventService = new EventService($eventRepository, $dateService, $this->seoService, $cacheService);

        $plugin = Helper::getPluginById(EventConfig::PLUGIN_ID);
        $this->tplPath = $plugin
            ? $plugin->getPaths()->getFrontendPath() . 'template/events/'
            : \PFAD_ROOT . 'plugins/' . EventConfig::PLUGIN_ID . '/frontend/template/events/';
    }

    /**
     * Event listing page: /veranstaltungen
     */
    public function listing(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->initServices();
        $this->smarty = $smarty;
        Shop::setPageType(\PAGE_PLUGIN);
        $this->init();
        $this->preRender();

        $languageIso = Shop::getLanguageISO();
        $params = $request->getQueryParams();

        $filter = EventListFilter::fromRequest($params, $languageIso);
        if ($filter->temporalStatus === null) {
            $filter->temporalStatus = 'upcoming';
        }

        $result = $this->eventService->getEventListing($filter);
        $categories = $this->loadCategories($languageIso);

        $smarty->assign([
            'events' => $result->events,
            'pagination' => $result,
            'filter' => $filter,
            'categories' => $categories,
            'listingUrl' => $this->seoService->getListingUrl(),
            'archiveUrl' => $this->seoService->getArchiveUrl(),
            'isArchive' => false,
            'bbfEventsPath' => $this->tplPath,
            'meta_title' => 'Veranstaltungen',
            'meta_description' => 'Alle Veranstaltungen im Überblick',
        ]);

        $html = $smarty->fetch($this->tplPath . 'listing.tpl');
        return new HtmlResponse($html);
    }

    /**
     * Archive page: /veranstaltungen/archiv
     */
    public function archive(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->initServices();
        $this->smarty = $smarty;
        Shop::setPageType(\PAGE_PLUGIN);
        $this->init();
        $this->preRender();

        $languageIso = Shop::getLanguageISO();
        $params = $request->getQueryParams();
        $params['status'] = 'past';

        $filter = EventListFilter::fromRequest($params, $languageIso);
        $result = $this->eventService->getEventListing($filter);
        $categories = $this->loadCategories($languageIso);

        $smarty->assign([
            'events' => $result->events,
            'pagination' => $result,
            'filter' => $filter,
            'categories' => $categories,
            'listingUrl' => $this->seoService->getListingUrl(),
            'archiveUrl' => $this->seoService->getArchiveUrl(),
            'isArchive' => true,
            'bbfEventsPath' => $this->tplPath,
            'meta_title' => 'Veranstaltungsarchiv',
            'meta_description' => 'Vergangene Veranstaltungen',
        ]);

        $html = $smarty->fetch($this->tplPath . 'listing.tpl');
        return new HtmlResponse($html);
    }

    /**
     * Category page: /veranstaltungen/kategorie/{slug}
     */
    public function category(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->initServices();
        $this->smarty = $smarty;
        Shop::setPageType(\PAGE_PLUGIN);
        $this->init();
        $this->preRender();

        $categorySlug = $args['slug'] ?? '';
        $languageIso = Shop::getLanguageISO();

        $category = $this->categoryRepository->findBySlug($categorySlug);
        if ($category === null || !$category->isActive) {
            return new HtmlResponse('', 404);
        }

        $this->resolveTranslation($category, $languageIso);

        $params = $request->getQueryParams();
        $filter = EventListFilter::fromRequest($params, $languageIso);
        $filter->categorySlug = $categorySlug;
        if ($filter->temporalStatus === null) {
            $filter->temporalStatus = 'upcoming';
        }

        $result = $this->eventService->getEventListing($filter);
        $allCategories = $this->loadCategories($languageIso);

        $smarty->assign([
            'events' => $result->events,
            'pagination' => $result,
            'filter' => $filter,
            'category' => $category,
            'categories' => $allCategories,
            'listingUrl' => $this->seoService->getListingUrl(),
            'bbfEventsPath' => $this->tplPath,
            'meta_title' => $category->translation?->metaTitle ?? $category->getName(),
            'meta_description' => $category->translation?->metaDescription ?? '',
        ]);

        $html = $smarty->fetch($this->tplPath . 'category.tpl');
        return new HtmlResponse($html);
    }

    /**
     * Detail page: /veranstaltungen/{slug}
     */
    public function detail(ServerRequestInterface $request, array $args, JTLSmarty $smarty): ResponseInterface
    {
        $this->initServices();
        $this->smarty = $smarty;
        Shop::setPageType(\PAGE_PLUGIN);
        $this->init();
        $this->preRender();

        $slug = $args['slug'] ?? '';
        $languageIso = Shop::getLanguageISO();

        $event = $this->eventService->getEventBySlug($slug, $languageIso);
        if ($event === null) {
            return new HtmlResponse('', 404);
        }

        // Pagebuilder output
        $pageResult = $this->buildPageOutput($event, $languageIso);

        // Tickets
        $ticketService = new TicketService($this->db);
        $tickets = $ticketService->getTicketsForEvent($event->id, $languageIso);

        // Schema.org
        $schema = SchemaOrgHelper::generateEventSchema($event, Shop::getURL());
        $schema = SchemaOrgHelper::addTicketOffers($schema, $tickets, Shop::getURL());
        $schemaJsonLd = SchemaOrgHelper::toJsonLd($schema);

        $smarty->assign([
            'event' => $event,
            'pageBuilderHtml' => $pageResult['html'],
            'pageBuilderCss' => $pageResult['css'],
            'tickets' => $tickets,
            'schemaJsonLd' => $schemaJsonLd,
            'bbfEventsPath' => $this->tplPath,
            'meta_title' => $this->seoService->getMetaTitle($event),
            'meta_description' => $this->seoService->getMetaDescription($event),
            'canonical_url' => Shop::getURL() . $this->seoService->getCanonicalUrl($event, $languageIso),
        ]);

        $html = $smarty->fetch($this->tplPath . 'detail.tpl');
        return new HtmlResponse($html);
    }

    // ── Helpers ───────────────────────────────────────────

    private function loadCategories(string $languageIso): array
    {
        $categories = $this->categoryRepository->findAll();
        foreach ($categories as $cat) {
            $this->resolveTranslation($cat, $languageIso);
        }
        return $categories;
    }

    private function resolveTranslation(EventCategory $cat, string $languageIso): void
    {
        foreach ($cat->translations as $t) {
            if ($t->languageIso === $languageIso) {
                $cat->translation = $t;
                return;
            }
        }
        if ($cat->translation === null && !empty($cat->translations)) {
            $cat->translation = $cat->translations[0];
        }
    }

    private function buildPageOutput(\Plugin\bbfdesign_events\src\Model\Event $event, string $languageIso): array
    {
        try {
            $pageRepo = new PagebuilderRepository($this->db);
            $programService = new ProgramService($this->db);
            $partnerService = new PartnerService($this->db);
            $ticketService = new TicketService($this->db);
            $knowledgeService = new KnowledgeService($this->db);
            $areaService = new AreaService($this->db);

            $pagebuilderService = new PagebuilderService(
                $pageRepo, $programService, $partnerService,
                $ticketService, $knowledgeService, $areaService
            );

            $result = $pagebuilderService->renderPage($event, $languageIso);
            return ['html' => $result->html, 'css' => $result->css];
        } catch (\Throwable) {
            return ['html' => '', 'css' => ''];
        }
    }
}
