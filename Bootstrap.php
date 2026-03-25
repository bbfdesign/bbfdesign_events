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

        $dispatcher->listen('shop.hook.' . \HOOK_SMARTY_OUTPUTFILTER, [SeoHook::class, 'handleRouting']);
        $dispatcher->listen('shop.hook.' . \HOOK_SITEMAP_EXPORT, [SeoHook::class, 'addToSitemap']);
        $dispatcher->listen('shop.hook.' . \HOOK_BACKEND_FUNCTIONS_SAVE, [CacheHook::class, 'invalidate']);
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