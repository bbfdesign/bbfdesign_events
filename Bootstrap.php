<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events;

use JTL\DB\DbInterface;
use JTL\Events\Dispatcher;
use JTL\Plugin\Bootstrapper;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Plugin\bbfdesign_events\src\Controller\Backend\AreaAdminController;
use Plugin\bbfdesign_events\src\Controller\Backend\CategoryAdminController;
use Plugin\bbfdesign_events\src\Controller\Backend\EventAdminController;
use Plugin\bbfdesign_events\src\Controller\Backend\KnowledgeAdminController;
use Plugin\bbfdesign_events\src\Controller\Backend\PartnerAdminController;
use Plugin\bbfdesign_events\src\Controller\Backend\TicketAdminController;
use Plugin\bbfdesign_events\src\Hook\SeoHook;
use Plugin\bbfdesign_events\src\Hook\SearchHook;
use Plugin\bbfdesign_events\src\Migration\Migration20260101000000;

class Bootstrap extends Bootstrapper
{
    public function boot(Dispatcher $dispatcher): void
    {
        parent::boot($dispatcher);

        if (Shop::isFrontend()) {
            $dispatcher->listen('shop.hook.140', static function (array $args) {
                SeoHook::handleRouting($args);
            });
        }

        $dispatcher->listen('bbf.search.index', static function (array $args) {
            SearchHook::provideSearchData($args);
        });
    }

    public function installed(): void
    {
        parent::installed();
        $this->runMigrations();
    }

    public function updated($oldVersion, $newVersion): void
    {
        parent::updated($oldVersion, $newVersion);
        $this->runMigrations();
    }

    public function uninstalled(bool $deleteData = true): void
    {
        if ($deleteData) {
            try {
                $db = Shop::Container()->getDB();
                $migration = new Migration20260101000000($db, $this->getPlugin()->getPluginID());
                $migration->down();
            } catch (\Throwable) {
            }
        }
        parent::uninstalled($deleteData);
    }

    public function renderAdminMenuTab(string $tabName, int $menuID, JTLSmarty $smarty): string
    {
        $plugin = $this->getPlugin();
        $db = Shop::Container()->getDB();
        $tplPath = $plugin->getPaths()->getAdminPath() . 'templates/';
        $postURL = Shop::getAdminURL() . '/plugin.php?kPlugin=' . $plugin->getID();

        $smarty->assign([
            'plugin'        => $plugin,
            'langVars'      => $plugin->getLocalization(),
            'postURL'       => $postURL,
            'tplPath'       => $tplPath,
            'ShopURL'       => Shop::getURL(),
            'adminUrl'      => $postURL,
            'pluginVersion' => $plugin->getCurrentVersion(),
            'db'            => $db,
        ]);

        // Handle AJAX
        if (isset($_REQUEST['is_ajax']) && (int) $_REQUEST['is_ajax'] === 1) {
            $this->handleAjax($db);
            exit;
        }

        $page = $_GET['bbf_page'] ?? 'events';
        $action = $_GET['action'] ?? 'list';
        $smarty->assign('activePage', $page);
        $smarty->assign('currentAction', $action);

        // Dispatch to controllers - they assign data to Smarty
        try {
            match ($page) {
                'events' => (new EventAdminController($db, $smarty, $postURL))->dispatch($action),
                'categories' => (new CategoryAdminController($db, $smarty, $postURL))->dispatch($action),
                'partners' => (new PartnerAdminController($db, $smarty, $postURL))->dispatch($action),
                'knowledge' => (new KnowledgeAdminController($db, $smarty, $postURL))->dispatch($action),
                'tickets' => (new TicketAdminController($db, $smarty, $postURL))->dispatch($action),
                'areas' => (new AreaAdminController($db, $smarty, $postURL))->dispatch($action),
                'settings' => $this->prepareSettings($smarty, $db),
                default => (new EventAdminController($db, $smarty, $postURL))->dispatch($action),
            };
        } catch (\Throwable $e) {
            $smarty->assign('error', $e->getMessage());
        }

        return $smarty->fetch($tplPath . 'admin.tpl');
    }

    private function prepareSettings(JTLSmarty $smarty, DbInterface $db): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'clear_cache') {
            $cache = Shop::Container()->getCache();
            $cacheService = new \Plugin\bbfdesign_events\src\Service\CacheService($cache);
            $cacheService->invalidateAll();
            $smarty->assign('msg', 'Cache geleert');
        }

        $smarty->assign('config', [
            'base_path' => \Plugin\bbfdesign_events\src\Config\EventConfig::BASE_PATH,
            'items_per_page' => \Plugin\bbfdesign_events\src\Config\EventConfig::ITEMS_PER_PAGE,
            'cache_ttl_listing' => \Plugin\bbfdesign_events\src\Config\EventConfig::CACHE_TTL_LISTING,
            'cache_ttl_detail' => \Plugin\bbfdesign_events\src\Config\EventConfig::CACHE_TTL_DETAIL,
            'media_base_dir' => \Plugin\bbfdesign_events\src\Config\EventConfig::MEDIA_BASE_DIR,
            'max_upload_size' => \Plugin\bbfdesign_events\src\Config\EventConfig::MAX_UPLOAD_SIZE / 1024 / 1024 . ' MB',
        ]);
    }

    private function handleAjax(DbInterface $db): void
    {
        header('Content-Type: application/json');
        $action = $_REQUEST['action'] ?? '';

        try {
            $result = match ($action) {
                'media_upload' => $this->handleMediaUpload(),
                'media_list' => $this->handleMediaList(),
                'page_load' => $this->handlePageLoad($db),
                'page_save' => $this->handlePageSave($db),
                default => ['success' => false, 'error' => 'Unknown action'],
            };
            echo json_encode($result, JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    private function handleMediaUpload(): array
    {
        $mediaService = new \Plugin\bbfdesign_events\src\Service\MediaService();
        $context = $_POST['context'] ?? 'images';
        $uploadedFiles = [];
        foreach ($_FILES['files']['tmp_name'] ?? [] as $i => $tmpName) {
            $result = $mediaService->upload([
                'tmp_name' => $tmpName,
                'name' => $_FILES['files']['name'][$i],
                'size' => $_FILES['files']['size'][$i],
            ], $context);
            if ($result !== null) {
                $uploadedFiles[] = $result;
            }
        }
        return ['success' => true, 'files' => $uploadedFiles];
    }

    private function handleMediaList(): array
    {
        return (new \Plugin\bbfdesign_events\src\Service\MediaService())->listFiles();
    }

    private function handlePageLoad(DbInterface $db): array
    {
        $repo = new \Plugin\bbfdesign_events\src\Repository\PagebuilderRepository($db);
        $page = $repo->findByEventAndLanguage((int) ($_GET['event_id'] ?? 0), $_GET['lang'] ?? 'ger');
        return ['success' => true, 'gjs_data' => $page?->gjsData, 'html_rendered' => $page?->htmlRendered, 'css_rendered' => $page?->cssRendered];
    }

    private function handlePageSave(DbInterface $db): array
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $repo = new \Plugin\bbfdesign_events\src\Repository\PagebuilderRepository($db);
        $page = new \Plugin\bbfdesign_events\src\Model\Pagebuilder\EventPage();
        $page->eventId = (int) ($input['event_id'] ?? 0);
        $page->languageIso = $input['language_iso'] ?? 'ger';
        $page->gjsData = $input['gjs_data'] ?? null;
        $page->htmlRendered = $input['html_rendered'] ?? null;
        $page->cssRendered = $input['css_rendered'] ?? null;
        $repo->savePage($page);
        return ['success' => true];
    }

    private function runMigrations(): void
    {
        try {
            $db = Shop::Container()->getDB();
            $result = $db->getSingleObject("SHOW TABLES LIKE 'bbf_events'");
            if ($result === null) {
                (new Migration20260101000000($db, $this->getPlugin()->getPluginID()))->up();
            }
        } catch (\Throwable) {
        }
    }
}
