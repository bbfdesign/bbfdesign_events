<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Hook;

use JTL\Plugin\PluginInterface;
use JTL\Shop;
use Plugin\bbfdesign_events\src\Config\EventConfig;
use Plugin\bbfdesign_events\src\Helper\SchemaOrgHelper;
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
 * Frontend hooks for BBF Events.
 * Pattern adapted from BBF FAQ / BBF Routes plugins.
 *
 * - includeAssets(): HOOK_LETZTERINCLUDE_CSS_JS – load CSS/JS
 * - injectSmartyData(): HOOK_SMARTY_INC – assign data to Smarty
 * - handleRouting(): HOOK_SMARTY_OUTPUTFILTER – detail page routing
 */
class SeoHook
{
    /**
     * HOOK_LETZTERINCLUDE_CSS_JS: Include frontend CSS/JS assets.
     */
    public static function includeAssets(array $args, PluginInterface $plugin): void
    {
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        $path = parse_url($requestUri, PHP_URL_PATH) ?? '';
        $path = ltrim($path, '/');

        if (!str_starts_with($path, EventConfig::BASE_PATH)) {
            return;
        }

        // CSS
        $frontendUrl = $plugin->getPaths()->getFrontendURL();
        $pq = $args['pq'] ?? null;
        if ($pq !== null) {
            $pq->find('head')->append(
                '<link rel="stylesheet" href="' . $frontendUrl . 'css/bbf-events.css">'
            );

            // Detail page gets additional CSS
            $seoService = new SeoService();
            $route = $seoService->resolveRoute($path);
            if ($route !== null && $route['type'] === 'detail') {
                $pq->find('head')->append(
                    '<link rel="stylesheet" href="' . $frontendUrl . 'css/bbf-events-detail.css">'
                );
            }
        }
    }

    /**
     * HOOK_SMARTY_INC: Inject event data into Smarty before template rendering.
     * This is called for FrontendLink pages (the listing page registered in info.xml).
     */
    public static function injectSmartyData(array $args, PluginInterface $plugin): void
    {
        $smarty = Shop::Smarty();

        try {
            $db = Shop::Container()->getDB();

            // Check if tables exist
            $tableCheck = $db->getSingleObject("SHOW TABLES LIKE 'bbf_events'");
            if ($tableCheck === null) {
                return;
            }

            $languageIso = Shop::getLanguageISO();
            $seoService = new SeoService();
            $eventRepository = new EventRepository($db);
            $categoryRepository = new EventCategoryRepository($db);
            $dateService = new EventDateService();
            $cacheService = new CacheService(Shop::Container()->getCache());
            $eventService = new EventService($eventRepository, $dateService, $seoService, $cacheService);

            $pluginPath = $plugin->getPaths()->getFrontendPath() . 'template/events/';

            // Always provide these for any event page
            $smarty->assign('bbfEventsPath', $pluginPath);
            $smarty->assign('ShopURL', Shop::getURL());
            $smarty->assign('listingUrl', $seoService->getListingUrl());
            $smarty->assign('archiveUrl', $seoService->getArchiveUrl());

            // Determine route from URL
            $requestUri = $_SERVER['REQUEST_URI'] ?? '';
            $path = ltrim(parse_url($requestUri, PHP_URL_PATH) ?? '', '/');

            if (!str_starts_with($path, EventConfig::BASE_PATH)) {
                return;
            }

            $route = $seoService->resolveRoute($path);
            if ($route === null) {
                return;
            }

            // Load categories for filter bar
            $categories = $categoryRepository->findAll();
            foreach ($categories as $cat) {
                foreach ($cat->translations as $t) {
                    if ($t->languageIso === $languageIso) {
                        $cat->translation = $t;
                        break;
                    }
                }
                if ($cat->translation === null && !empty($cat->translations)) {
                    $cat->translation = $cat->translations[0];
                }
            }
            $smarty->assign('categories', $categories);

            match ($route['type']) {
                'listing' => self::prepareListingData($smarty, $eventService, $languageIso, false),
                'archive' => self::prepareListingData($smarty, $eventService, $languageIso, true),
                'category' => self::prepareCategoryData($smarty, $eventService, $categoryRepository, $languageIso, $route['slug']),
                'detail' => self::prepareDetailData($smarty, $eventService, $db, $plugin, $languageIso, $route['slug']),
                default => null,
            };
        } catch (\Throwable $e) {
            // Don't crash the shop
        }
    }

    /**
     * HOOK_SMARTY_OUTPUTFILTER: Handle sub-page routing.
     * The FrontendLink only handles /veranstaltungen (listing).
     * Detail, category, archive pages need output filter to replace content.
     */
    public static function handleRouting(array $args): void
    {
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        $path = ltrim(parse_url($requestUri, PHP_URL_PATH) ?? '', '/');

        if (!str_starts_with($path, EventConfig::BASE_PATH)) {
            return;
        }

        $seoService = new SeoService();
        $route = $seoService->resolveRoute($path);

        // Listing is handled by the FrontendLink template
        if ($route === null || $route['type'] === 'listing') {
            return;
        }

        // For detail/category/archive: render the correct template
        // and replace the page output
        try {
            $smarty = Shop::Smarty();
            $plugin = \JTL\Plugin\Helper::getPluginById(EventConfig::PLUGIN_ID);
            if ($plugin === null) {
                return;
            }

            $tplPath = $plugin->getPaths()->getFrontendPath() . 'template/events/';
            $smarty->assign('bbfEventsPath', $tplPath);

            $templateFile = match ($route['type']) {
                'detail' => 'detail.tpl',
                'category' => 'category.tpl',
                'archive' => 'listing.tpl',
                default => null,
            };

            if ($templateFile !== null && is_file($tplPath . $templateFile)) {
                // Data was already injected by injectSmartyData() in HOOK_SMARTY_INC
                $content = $smarty->fetch($tplPath . $templateFile);

                // Replace the main content area in the JTL output
                if (isset($args['original'])) {
                    // Find the content area and replace it
                    $args['original'] = $content;
                }
            }
        } catch (\Throwable) {
            // Don't crash
        }
    }

    // ── Data Preparation Methods ──────────────────────────

    private static function prepareListingData(
        \Smarty $smarty,
        EventService $eventService,
        string $languageIso,
        bool $isArchive
    ): void {
        $params = $_GET;
        if ($isArchive) {
            $params['status'] = 'past';
        }

        $filter = EventListFilter::fromRequest($params, $languageIso);
        if (!$isArchive && $filter->temporalStatus === null) {
            $filter->temporalStatus = 'upcoming';
        }

        $result = $eventService->getEventListing($filter);

        $smarty->assign('events', $result->events);
        $smarty->assign('pagination', $result);
        $smarty->assign('filter', $filter);
        $smarty->assign('isArchive', $isArchive);
    }

    private static function prepareCategoryData(
        \Smarty $smarty,
        EventService $eventService,
        EventCategoryRepository $categoryRepository,
        string $languageIso,
        string $categorySlug
    ): void {
        $category = $categoryRepository->findBySlug($categorySlug);
        if ($category === null) {
            return;
        }

        foreach ($category->translations as $t) {
            if ($t->languageIso === $languageIso) {
                $category->translation = $t;
                break;
            }
        }
        if ($category->translation === null && !empty($category->translations)) {
            $category->translation = $category->translations[0];
        }

        $filter = EventListFilter::fromRequest($_GET, $languageIso);
        $filter->categorySlug = $categorySlug;
        if ($filter->temporalStatus === null) {
            $filter->temporalStatus = 'upcoming';
        }

        $result = $eventService->getEventListing($filter);

        $smarty->assign('category', $category);
        $smarty->assign('events', $result->events);
        $smarty->assign('pagination', $result);
        $smarty->assign('filter', $filter);
    }

    private static function prepareDetailData(
        \Smarty $smarty,
        EventService $eventService,
        \JTL\DB\DbInterface $db,
        PluginInterface $plugin,
        string $languageIso,
        string $slug
    ): void {
        $event = $eventService->getEventBySlug($slug, $languageIso);
        if ($event === null) {
            return;
        }

        // Pagebuilder output
        try {
            $pageRepo = new PagebuilderRepository($db);
            $programService = new ProgramService($db);
            $partnerService = new PartnerService($db);
            $ticketService = new TicketService($db);
            $knowledgeService = new KnowledgeService($db);
            $areaService = new AreaService($db);

            $pagebuilderService = new PagebuilderService(
                $pageRepo, $programService, $partnerService,
                $ticketService, $knowledgeService, $areaService
            );

            $pageResult = $pagebuilderService->renderPage($event, $languageIso);
            $smarty->assign('pageBuilderHtml', $pageResult->html);
            $smarty->assign('pageBuilderCss', $pageResult->css);
        } catch (\Throwable) {
            $smarty->assign('pageBuilderHtml', '');
            $smarty->assign('pageBuilderCss', '');
        }

        // Tickets
        $ticketService = new TicketService($db);
        $tickets = $ticketService->getTicketsForEvent($event->id, $languageIso);

        // Schema.org
        $schema = SchemaOrgHelper::generateEventSchema($event, Shop::getURL());
        $schema = SchemaOrgHelper::addTicketOffers($schema, $tickets, Shop::getURL());
        $schemaJsonLd = SchemaOrgHelper::toJsonLd($schema);

        $smarty->assign('event', $event);
        $smarty->assign('tickets', $tickets);
        $smarty->assign('schemaJsonLd', $schemaJsonLd);
    }

    /**
     * Sitemap integration.
     */
    public static function addToSitemap(array $args): void
    {
        $db = Shop::Container()->getDB();

        try {
            $tableCheck = $db->getSingleObject("SHOW TABLES LIKE 'bbf_events'");
            if ($tableCheck === null) {
                return;
            }
        } catch (\Throwable) {
            return;
        }

        $seoService = new SeoService();
        $baseUrl = Shop::getURL();

        $args['sitemap'][] = [
            'loc' => $baseUrl . $seoService->getListingUrl(),
            'changefreq' => 'daily',
            'priority' => '0.8',
        ];

        $rows = $db->getObjects(
            "SELECT e.slug, e.updated_at FROM bbf_events e WHERE e.status = 'published'"
        );

        foreach ($rows as $row) {
            $args['sitemap'][] = [
                'loc' => $baseUrl . '/' . EventConfig::BASE_PATH . '/' . $row->slug,
                'lastmod' => date('Y-m-d', strtotime($row->updated_at)),
                'changefreq' => 'weekly',
                'priority' => '0.7',
            ];
        }

        $catRows = $db->getObjects('SELECT slug FROM bbf_event_categories WHERE is_active = 1');
        foreach ($catRows as $row) {
            $args['sitemap'][] = [
                'loc' => $baseUrl . '/' . EventConfig::BASE_PATH . '/kategorie/' . $row->slug,
                'changefreq' => 'weekly',
                'priority' => '0.6',
            ];
        }
    }
}
