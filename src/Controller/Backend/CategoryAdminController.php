<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Controller\Backend;

use JTL\Shop;
use Plugin\bbfdesign_events\src\Config\EventConfig;
use Plugin\bbfdesign_events\src\Helper\SlugHelper;
use Plugin\bbfdesign_events\src\Model\EventCategory;
use Plugin\bbfdesign_events\src\Model\EventCategoryTranslation;
use Plugin\bbfdesign_events\src\Repository\EventCategoryRepository;

class CategoryAdminController
{
    private EventCategoryRepository $repository;
    private string $templatePath;

    public function __construct()
    {
        $db = Shop::Container()->getDB();
        $this->repository = new EventCategoryRepository($db);
        $this->templatePath = EventConfig::getPluginPath() . 'adminmenu/templates/categories/';
    }

    public function dispatch(): void
    {
        $action = $_GET['action'] ?? 'list';
        $smarty = Shop::Smarty();

        match ($action) {
            'create' => $this->showForm($smarty),
            'edit' => $this->showForm($smarty, (int) ($_GET['id'] ?? 0)),
            'save' => $this->save(),
            'delete' => $this->delete(),
            default => $this->showList($smarty),
        };
    }

    private function showList(\Smarty $smarty): void
    {
        $categories = $this->repository->findAll(false);

        foreach ($categories as $cat) {
            foreach ($cat->translations as $t) {
                if ($t->languageIso === EventConfig::DEFAULT_LANGUAGE) {
                    $cat->translation = $t;
                    break;
                }
            }
            if ($cat->translation === null && !empty($cat->translations)) {
                $cat->translation = $cat->translations[0];
            }
        }

        $smarty->assign('categories', $categories);
        $smarty->assign('repository', $this->repository);
        $smarty->display($this->templatePath . 'list.tpl');
    }

    private function showForm(\Smarty $smarty, int $id = 0): void
    {
        $category = $id > 0 ? $this->repository->findById($id) : new EventCategory();

        if ($id > 0 && $category === null) {
            header('Location: ?action=list&error=notfound');
            return;
        }

        $allCategories = $this->repository->findAll(false);
        foreach ($allCategories as $cat) {
            foreach ($cat->translations as $t) {
                if ($t->languageIso === EventConfig::DEFAULT_LANGUAGE) {
                    $cat->translation = $t;
                    break;
                }
            }
        }

        $db = Shop::Container()->getDB();
        $languages = $db->getObjects(
            "SELECT cISO as iso, cNameDeutsch as name FROM tsprache WHERE active = 1 ORDER BY cISO"
        );

        $smarty->assign('category', $category);
        $smarty->assign('allCategories', $allCategories);
        $smarty->assign('languages', $languages ?: [(object) ['iso' => 'ger', 'name' => 'Deutsch']]);
        $smarty->assign('isEdit', $id > 0);
        $smarty->display($this->templatePath . 'edit.tpl');
    }

    private function save(): void
    {
        $id = (int) ($_POST['category_id'] ?? 0);
        $isNew = $id === 0;

        $category = $isNew ? new EventCategory() : $this->repository->findById($id);
        if (!$isNew && $category === null) {
            header('Location: ?action=list&error=notfound');
            return;
        }

        $category->id = $id;
        $category->parentId = ($_POST['parent_id'] ?? '') !== '' ? (int) $_POST['parent_id'] : null;
        $category->sortOrder = (int) ($_POST['sort_order'] ?? 0);
        $category->isActive = isset($_POST['is_active']);
        $category->image = ($_POST['image'] ?? '') !== '' ? $_POST['image'] : null;

        // Generate slug from first translation name
        $firstName = $_POST['trans_ger_name'] ?? $_POST['trans_' . EventConfig::DEFAULT_LANGUAGE . '_name'] ?? '';
        $category->slug = ($_POST['slug'] ?? '') !== ''
            ? $_POST['slug']
            : SlugHelper::generate($firstName);

        $category->slug = SlugHelper::ensureUnique(
            $category->slug,
            fn(string $s) => $this->repository->slugExists($s, $isNew ? null : $id)
        );

        $savedId = $this->repository->save($category);

        // Save translations
        $db = Shop::Container()->getDB();
        $languages = $db->getObjects(
            "SELECT cISO as iso FROM tsprache WHERE active = 1"
        );

        foreach ($languages ?: [(object) ['iso' => 'ger']] as $lang) {
            $iso = $lang->iso;
            $name = $_POST['trans_' . $iso . '_name'] ?? '';

            if ($name === '') {
                continue;
            }

            $t = new EventCategoryTranslation();
            $t->categoryId = $savedId;
            $t->languageIso = $iso;
            $t->name = trim($name);
            $t->description = ($_POST['trans_' . $iso . '_description'] ?? '') !== '' ? $_POST['trans_' . $iso . '_description'] : null;
            $t->metaTitle = ($_POST['trans_' . $iso . '_meta_title'] ?? '') !== '' ? $_POST['trans_' . $iso . '_meta_title'] : null;
            $t->metaDescription = ($_POST['trans_' . $iso . '_meta_description'] ?? '') !== '' ? $_POST['trans_' . $iso . '_meta_description'] : null;

            // Check for existing
            $existing = $db->getSingleObject(
                'SELECT id FROM bbf_event_categories_translation WHERE category_id = :cid AND language_iso = :lang',
                ['cid' => $savedId, 'lang' => $iso]
            );
            if ($existing) {
                $t->id = (int) $existing->id;
            }

            $this->repository->saveTranslation($t);
        }

        $msg = $isNew ? 'created' : 'updated';
        header('Location: ?action=edit&id=' . $savedId . '&msg=' . $msg);
    }

    private function delete(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id > 0) {
            $this->repository->delete($id);
        }
        header('Location: ?action=list&msg=deleted');
    }
}
