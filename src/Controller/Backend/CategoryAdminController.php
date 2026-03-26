<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Controller\Backend;

use JTL\DB\DbInterface;
use JTL\Smarty\JTLSmarty;
use Plugin\bbfdesign_events\src\Config\EventConfig;
use Plugin\bbfdesign_events\src\Helper\SlugHelper;
use Plugin\bbfdesign_events\src\Model\EventCategory;
use Plugin\bbfdesign_events\src\Model\EventCategoryTranslation;
use Plugin\bbfdesign_events\src\Repository\EventCategoryRepository;

class CategoryAdminController
{
    private EventCategoryRepository $repository;

    public function __construct(
        private readonly DbInterface $db,
        private readonly JTLSmarty $smarty,
        private readonly string $postURL
    ) {
        $this->repository = new EventCategoryRepository($this->db);
    }

    public function dispatch(string $action): void
    {
        match ($action) {
            'create' => $this->prepareForm(0),
            'edit' => $this->prepareForm((int) ($_GET['id'] ?? 0)),
            'save' => $this->handleSave(),
            'delete' => $this->handleDelete(),
            default => $this->prepareList(),
        };
    }

    private function prepareList(): void
    {
        $categories = $this->repository->findAll(false);
        foreach ($categories as $cat) {
            $this->resolveTranslation($cat);
        }

        $this->smarty->assign('categories', $categories);
        $this->smarty->assign('repository', $this->repository);
    }

    private function prepareForm(int $id): void
    {
        $category = $id > 0 ? $this->repository->findById($id) : new EventCategory();
        if ($id > 0 && $category === null) {
            header('Location: ' . $this->buildUrl('categories') . '&error=notfound');
            exit;
        }

        $allCategories = $this->repository->findAll(false);
        foreach ($allCategories as $cat) {
            $this->resolveTranslation($cat);
        }

        $languages = $this->getLanguages();

        $this->smarty->assign('category', $category);
        $this->smarty->assign('allCategories', $allCategories);
        $this->smarty->assign('languages', $languages);
        $this->smarty->assign('isEdit', $id > 0);
        $this->smarty->assign('activePage', 'category_edit');
    }

    private function handleSave(): void
    {
        $id = (int) ($_POST['category_id'] ?? 0);
        $isNew = $id === 0;

        $category = $isNew ? new EventCategory() : $this->repository->findById($id);
        if (!$isNew && $category === null) {
            header('Location: ' . $this->buildUrl('categories') . '&error=notfound');
            exit;
        }

        $category->id = $id;
        $category->parentId = ($_POST['parent_id'] ?? '') !== '' ? (int) $_POST['parent_id'] : null;
        $category->sortOrder = (int) ($_POST['sort_order'] ?? 0);
        $category->isActive = isset($_POST['is_active']);
        $category->image = ($_POST['image'] ?? '') !== '' ? $_POST['image'] : null;

        $firstName = $_POST['trans_ger_name'] ?? '';
        $category->slug = ($_POST['slug'] ?? '') !== '' ? $_POST['slug'] : SlugHelper::generate($firstName);
        $category->slug = SlugHelper::ensureUnique(
            $category->slug,
            fn(string $s) => $this->repository->slugExists($s, $isNew ? null : $id)
        );

        $savedId = $this->repository->save($category);

        foreach ($this->getLanguages() as $lang) {
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

            $existing = $this->db->getSingleObject(
                'SELECT id FROM bbf_event_categories_translation WHERE category_id = :cid AND language_iso = :lang',
                ['cid' => $savedId, 'lang' => $iso]
            );
            if ($existing) {
                $t->id = (int) $existing->id;
            }
            $this->repository->saveTranslation($t);
        }

        header('Location: ' . $this->buildUrl('categories', 'edit', $savedId) . '&msg=' . ($isNew ? 'created' : 'updated'));
        exit;
    }

    private function handleDelete(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id > 0) {
            $this->repository->delete($id);
        }
        header('Location: ' . $this->buildUrl('categories') . '&msg=deleted');
        exit;
    }

    private function resolveTranslation(EventCategory $cat): void
    {
        foreach ($cat->translations as $t) {
            if ($t->languageIso === EventConfig::DEFAULT_LANGUAGE) {
                $cat->translation = $t;
                return;
            }
        }
        if (!empty($cat->translations)) {
            $cat->translation = $cat->translations[0];
        }
    }

    private function getLanguages(): array
    {
        $rows = $this->db->getObjects("SELECT cISO as iso, cNameDeutsch as name FROM tsprache WHERE active = 1 ORDER BY cISO");
        return $rows ?: [(object) ['iso' => 'ger', 'name' => 'Deutsch']];
    }

    private function buildUrl(string $page, string $action = 'list', ?int $id = null): string
    {
        $url = $this->postURL . '&bbf_page=' . $page;
        if ($action !== 'list') {
            $url .= '&action=' . $action;
        }
        if ($id !== null) {
            $url .= '&id=' . $id;
        }
        return $url;
    }
}
