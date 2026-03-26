<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Repository;

use Plugin\bbfdesign_events\src\Config\EventConfig;
use Plugin\bbfdesign_events\src\Enum\EventStatus;

class EventListFilter
{
    public ?EventStatus $status = null;
    public ?string $categorySlug = null;
    public ?bool $isFeatured = null;
    public ?string $temporalStatus = null; // 'upcoming', 'past', 'all'
    public ?\DateTimeImmutable $dateFrom = null;
    public ?\DateTimeImmutable $dateTo = null;
    public ?string $searchQuery = null;
    public string $sortBy = 'date_asc';
    public int $page = 1;
    public int $perPage;
    public string $languageIso = 'ger';
    public bool $showAllStatuses = false;

    public function __construct()
    {
        $this->perPage = EventConfig::ITEMS_PER_PAGE;
    }

    public static function fromRequest(array $params, string $languageIso = 'ger'): self
    {
        $filter = new self();
        $filter->languageIso = $languageIso;

        if (isset($params['status']) && $params['status'] !== 'all') {
            $filter->temporalStatus = $params['status'];
        }

        if (isset($params['category']) && $params['category'] !== '') {
            $filter->categorySlug = $params['category'];
        }

        if (isset($params['date_from']) && $params['date_from'] !== '') {
            $filter->dateFrom = \DateTimeImmutable::createFromFormat('Y-m-d', $params['date_from']) ?: null;
        }

        if (isset($params['date_to']) && $params['date_to'] !== '') {
            $filter->dateTo = \DateTimeImmutable::createFromFormat('Y-m-d', $params['date_to']) ?: null;
        }

        if (isset($params['q']) && $params['q'] !== '') {
            $filter->searchQuery = trim($params['q']);
        }

        if (isset($params['sort']) && in_array($params['sort'], ['date_asc', 'date_desc', 'title', 'featured'], true)) {
            $filter->sortBy = $params['sort'];
        }

        if (isset($params['page'])) {
            $filter->page = max(1, (int) $params['page']);
        }

        return $filter;
    }
}
