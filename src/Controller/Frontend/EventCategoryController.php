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

class EventCategoryController
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
        $categorySlug = $route['slug'] ?? '';
        $languageIso = Shop::getLanguageISO();
        $smarty = Shop::Smarty();

        $category = $this->categoryRepository->findBySlug($categorySlug);

        if ($category === null || !$category->isActive) {
            http_response_code(404);
            return;
        }

        // Resolve translation
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

        $result = $this->eventService->getEventListing($filter);

        // All categories for filter bar
        $allCategories = $this->categoryRepository->findAll();
        foreach ($allCategories as $cat) {
            foreach ($cat->translations as $t) {
                if ($t->languageIso === $languageIso) {
                    $cat->translation = $t;
                    break;
                }
            }
        }

        $pluginTemplatePath = EventConfig::getPluginPath() . 'frontend/template/events/';

        $smarty->assign('events', $result->events);
        $smarty->assign('pagination', $result);
        $smarty->assign('filter', $filter);
        $smarty->assign('category', $category);
        $smarty->assign('categories', $allCategories);
        $smarty->assign('listingUrl', $this->seoService->getListingUrl());
        $smarty->assign('bbfEventsPath', $pluginTemplatePath);

        // Meta
        $smarty->assign('meta_title', $category->translation?->metaTitle ?? $category->getName());
        $smarty->assign('meta_description', $category->translation?->metaDescription ?? '');

        $smarty->assign('bbfEventsCss', [
            '/' . EventConfig::getFrontendCssPath() . 'bbf-events.css',
            '/' . EventConfig::getFrontendCssPath() . 'bbf-events-listing.css',
        ]);

        $smarty->display($pluginTemplatePath . 'category.tpl');
    }
}
