<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events;

use JTL\Events\Dispatcher;
use JTL\Plugin\Bootstrapper;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Plugin\bbfdesign_events\src\Hook\SeoHook;
use Plugin\bbfdesign_events\src\Hook\SearchHook;
use Plugin\bbfdesign_events\src\Migration\Migration20260101000000;

class Bootstrap extends Bootstrapper
{
    public function boot(Dispatcher $dispatcher): void
    {
        parent::boot($dispatcher);

        if (Shop::isFrontend()) {
            // Hook 140: HOOK_SMARTY_OUTPUTFILTER – SEO URL Routing
            $dispatcher->listen('shop.hook.140', static function (array $args) {
                SeoHook::handleRouting($args);
            });
        }

        // Custom Event: BBF Search Plugin Integration
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
            } catch (\Throwable $e) {
                // Silently fail on uninstall
            }
        }
        parent::uninstalled($deleteData);
    }

    /**
     * Renders the admin menu tab content.
     * JTL calls this method when the plugin's admin page is opened.
     */
    public function renderAdminMenuTab(string $tabName, int $menuID, JTLSmarty $smarty): string
    {
        $plugin = $this->getPlugin();
        $db = Shop::Container()->getDB();
        $tplPath = $plugin->getPaths()->getAdminPath() . 'templates/';
        $adminUrl = $plugin->getPaths()->getAdminURL();

        $smarty->assign([
            'plugin'        => $plugin,
            'langVars'      => $plugin->getLocalization(),
            'postURL'       => $plugin->getPaths()->getBackendURL(),
            'tplPath'       => $tplPath,
            'ShopURL'       => Shop::getURL(),
            'adminUrl'      => $adminUrl,
            'pluginVersion' => $plugin->getCurrentVersion(),
            'db'            => $db,
        ]);

        // Handle AJAX requests
        if (isset($_REQUEST['is_ajax']) && (int) $_REQUEST['is_ajax'] === 1) {
            $this->handleAjax($db);
            exit;
        }

        // Determine active page from GET param
        $page = $_GET['bbf_page'] ?? 'events';
        $smarty->assign('activePage', $page);

        return $smarty->fetch($tplPath . 'admin.tpl');
    }

    private function handleAjax(\JTL\DB\DbInterface $db): void
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
            $file = [
                'tmp_name' => $tmpName,
                'name' => $_FILES['files']['name'][$i],
                'size' => $_FILES['files']['size'][$i],
            ];
            $result = $mediaService->upload($file, $context);
            if ($result !== null) {
                $uploadedFiles[] = $result;
            }
        }

        return ['success' => true, 'files' => $uploadedFiles];
    }

    private function handleMediaList(): array
    {
        $mediaService = new \Plugin\bbfdesign_events\src\Service\MediaService();
        return $mediaService->listFiles();
    }

    private function handlePageLoad(\JTL\DB\DbInterface $db): array
    {
        $eventId = (int) ($_GET['event_id'] ?? 0);
        $lang = $_GET['lang'] ?? 'ger';
        $repo = new \Plugin\bbfdesign_events\src\Repository\PagebuilderRepository($db);
        $page = $repo->findByEventAndLanguage($eventId, $lang);

        return [
            'success' => true,
            'gjs_data' => $page?->gjsData,
            'html_rendered' => $page?->htmlRendered,
            'css_rendered' => $page?->cssRendered,
        ];
    }

    private function handlePageSave(\JTL\DB\DbInterface $db): array
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
                $migration = new Migration20260101000000($db, $this->getPlugin()->getPluginID());
                $migration->up();
            }
        } catch (\Throwable $e) {
            // Log error but don't crash
        }
    }
}
