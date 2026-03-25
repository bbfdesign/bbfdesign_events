<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Hook;

use JTL\Shop;
use Plugin\bbfdesign_events\src\Service\CacheService;

class CacheHook
{
    public static function invalidate(array $args): void
    {
        $cache = Shop::Container()->getCache();
        $cacheService = new CacheService($cache);
        $cacheService->invalidateAll();
    }
}
