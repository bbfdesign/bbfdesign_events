<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Controller\Backend;

use JTL\Shop;
use Plugin\bbfdesign_events\src\Config\EventConfig;
use Plugin\bbfdesign_events\src\Service\CacheService;

class SettingsAdminController
{
    private string $templatePath;

    public function __construct()
    {
        $this->templatePath = EventConfig::getPluginPath() . 'adminmenu/templates/settings/';
    }

    public function dispatch(): void
    {
        $action = $_POST['action'] ?? 'show';
        $smarty = Shop::Smarty();

        if ($action === 'clear_cache') {
            $cache = Shop::Container()->getCache();
            $cacheService = new CacheService($cache);
            $cacheService->invalidateAll();
            $smarty->assign('msg', 'Cache geleert');
        }

        $smarty->assign('config', [
            'base_path' => EventConfig::BASE_PATH,
            'items_per_page' => EventConfig::ITEMS_PER_PAGE,
            'cache_ttl_listing' => EventConfig::CACHE_TTL_LISTING,
            'cache_ttl_detail' => EventConfig::CACHE_TTL_DETAIL,
            'media_base_dir' => EventConfig::MEDIA_BASE_DIR,
            'max_upload_size' => EventConfig::MAX_UPLOAD_SIZE / 1024 / 1024 . ' MB',
        ]);

        $smarty->display($this->templatePath . 'index.tpl');
    }
}
