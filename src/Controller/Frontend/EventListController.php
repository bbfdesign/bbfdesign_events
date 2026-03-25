<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Controller\Frontend;

use JTL\Shop;
use Plugin\bbfdesign_events\src\Config\EventConfig;
use Plugin\bbfdesign_events\src\Repository\EventCategoryRepository;
use Plugin\bbfdesign_events\src\Repository\EventListFilter;
use Plugin\bbfdesign_events\src\Service\CacheService;
use Plugin\bbfdesign_events\src\Service\EventDateService;
use Plugin\bbfdesign_events\src\Service\EventService;
use Plugin\bbfdesign_events\src\Service\SeoService;

class EventListController
{
    private EventService $eventService;
    private SeoService $seoService;
    private EventCategoryRepository $categoryRepository;

    public function __construct()
    {
        $db = Shop::Container()->getDB();
        $cache = Shop::Container()->getCache();

        $this->seoService = new SeoService();
        $this->categoryRepository = new EventCategoryRepository($db);

        $eventRepository = new \Plugin\bbfdesign_events\src\Repository\EventRepository($db);
        $dateService = new EventDateService();
        $cacheService = new CacheService($cache);

        $this->eventService = new EventService($eventRepository, $dateService, $this->seoService, $cacheService);
    }

    public function dispatch(array $route): void
    {
        $languageIso = Shop::getLanguageISO();
        $smarty = Shop::Smarty();
        $params = $_GET;

        // Archive mode
        if ($route['type'] === 'archive') {
            $params['status'] = 'past';
        }

        $filter = EventListFilter::fromRequest($params, $languageIso);

        // Default to upcoming for listing
        if ($route['type'] === 'listing' && $filter->temporalStatus === null) {
            $filter->temporalStatus = 'upcoming';
        }

        $result = $this->eventService->getEventListing($filter);
        $categories = $this->categoryRepository->findAll();

        // Resolve translations for categories
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

        $pluginTemplatePath = EventConfig::getPluginPath() . 'frontend/template/events/';

        $smarty->assign('events', $result->events);
        $smarty->assign('pagination', $result);
        $smarty->assign('filter', $filter);
        $smarty->assign('categories', $categories);
        $smarty->assign('listingUrl', $this->seoService->getListingUrl());
        $smarty->assign('archiveUrl', $this->seoService->getArchiveUrl());
        $smarty->assign('isArchive', $route['type'] === 'archive');
        $smarty->assign('bbfEventsPath', $pluginTemplatePath);

        // Meta
        $pageTitle = $route['type'] === 'archive' ? 'Veranstaltungsarchiv' : 'Veranstaltungen';
        $smarty->assign('meta_title', $pageTitle);
        $smarty->assign('meta_description', 'Alle Veranstaltungen im Überblick');

        // CSS
        $smarty->assign('bbfEventsCss', [
            '/' . EventConfig::getFrontendCssPath() . 'bbf-events.css',
            '/' . EventConfig::getFrontendCssPath() . 'bbf-events-listing.css',
        ]);

        $smarty->display($pluginTemplatePath . 'listing.tpl');
    }
}
