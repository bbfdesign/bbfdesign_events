<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Hook;

use JTL\Shop;
use Plugin\bbfdesign_events\src\Config\EventConfig;
use Plugin\bbfdesign_events\src\Service\SeoService;

class SeoHook
{
    public static function handleRouting(array $args): void
    {
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        $path = parse_url($requestUri, PHP_URL_PATH) ?? '';
        $path = ltrim($path, '/');

        // Only handle our routes
        if (!str_starts_with($path, EventConfig::BASE_PATH)) {
            return;
        }

        $seoService = new SeoService();
        $route = $seoService->resolveRoute($path);

        if ($route === null) {
            return;
        }

        // Store route info for controller dispatch
        $_SESSION['bbf_events_route'] = $route;

        // Prevent JTL from handling this as a 404
        $args['bSeite'] = true;

        // Dispatch to appropriate controller
        $controllerClass = match ($route['type']) {
            'listing', 'archive' => \Plugin\bbfdesign_events\src\Controller\Frontend\EventListController::class,
            'category' => \Plugin\bbfdesign_events\src\Controller\Frontend\EventCategoryController::class,
            'detail' => \Plugin\bbfdesign_events\src\Controller\Frontend\EventDetailController::class,
            default => null,
        };

        if ($controllerClass !== null && class_exists($controllerClass)) {
            $controller = new $controllerClass();
            $controller->dispatch($route);
        }
    }

    public static function addToSitemap(array $args): void
    {
        $db = Shop::Container()->getDB();

        // Check if tables exist before querying
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

        // Add listing page
        $args['sitemap'][] = [
            'loc' => $baseUrl . $seoService->getListingUrl(),
            'changefreq' => 'daily',
            'priority' => '0.8',
        ];

        // Add published events
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

        // Add categories
        $catRows = $db->getObjects(
            'SELECT slug FROM bbf_event_categories WHERE is_active = 1'
        );

        foreach ($catRows as $row) {
            $args['sitemap'][] = [
                'loc' => $baseUrl . '/' . EventConfig::BASE_PATH . '/kategorie/' . $row->slug,
                'changefreq' => 'weekly',
                'priority' => '0.6',
            ];
        }
    }
}
