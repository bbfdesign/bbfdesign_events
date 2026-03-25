<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events;

use JTL\Events\Dispatcher;
use JTL\Plugin\Bootstrapper;
use Plugin\bbfdesign_events\src\Hook\SeoHook;
use Plugin\bbfdesign_events\src\Hook\CacheHook;
use Plugin\bbfdesign_events\src\Hook\SearchHook;

class Bootstrap extends Bootstrapper
{
    public function boot(Dispatcher $dispatcher): void
    {
        parent::boot($dispatcher);

        // Hook 140: HOOK_SMARTY_OUTPUTFILTER – SEO URL Routing
        $dispatcher->listen('shop.hook.140', [SeoHook::class, 'handleRouting']);

        // Hook 142: HOOK_SITEMAP_EXPORT_BUILDINDEX – Sitemap Integration
        $dispatcher->listen('shop.hook.142', [SeoHook::class, 'addToSitemap']);

        // Hook 99: HOOK_BACKEND_FUNCTIONS_AFTER – Cache Invalidierung
        $dispatcher->listen('shop.hook.99', [CacheHook::class, 'invalidate']);

        // Custom Event: BBF Search Plugin Integration
        $dispatcher->listen('bbf.search.index', [SearchHook::class, 'provideSearchData']);
    }

    public function installed(): void
    {
        parent::installed();
    }

    public function updated($oldVersion, $newVersion): void
    {
        parent::updated($oldVersion, $newVersion);
    }

    public function uninstalled(bool $deleteData = true): void
    {
        parent::uninstalled($deleteData);
    }

    public function enabled(): void
    {
        parent::enabled();
    }

    public function disabled(): void
    {
        parent::disabled();
    }
}