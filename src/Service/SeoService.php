<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Service;

use Plugin\bbfdesign_events\src\Config\EventConfig;
use Plugin\bbfdesign_events\src\Model\Event;
use Plugin\bbfdesign_events\src\Model\EventCategory;

class SeoService
{
    public function getEventUrl(Event $event, string $languageIso = 'ger'): string
    {
        $slug = $event->translation?->slugLocalized ?? $event->slug;
        return '/' . EventConfig::BASE_PATH . '/' . $slug;
    }

    public function getCategoryUrl(EventCategory $category): string
    {
        return '/' . EventConfig::BASE_PATH . '/kategorie/' . $category->slug;
    }

    public function getListingUrl(): string
    {
        return '/' . EventConfig::BASE_PATH;
    }

    public function getArchiveUrl(): string
    {
        return '/' . EventConfig::BASE_PATH . '/archiv';
    }

    public function resolveRoute(string $path): ?array
    {
        $basePath = EventConfig::BASE_PATH;

        // Remove leading slash
        $path = ltrim($path, '/');

        // Listing
        if ($path === $basePath) {
            return ['type' => 'listing'];
        }

        // Archive
        if ($path === $basePath . '/archiv') {
            return ['type' => 'archive'];
        }

        // Category
        if (preg_match('#^' . preg_quote($basePath) . '/kategorie/([a-z0-9-]+)$#', $path, $m)) {
            return ['type' => 'category', 'slug' => $m[1]];
        }

        // Event detail
        if (preg_match('#^' . preg_quote($basePath) . '/([a-z0-9-]+)$#', $path, $m)) {
            return ['type' => 'detail', 'slug' => $m[1]];
        }

        return null;
    }

    public function getMetaTitle(Event $event): string
    {
        return $event->translation?->metaTitle
            ?? $event->getTitle();
    }

    public function getMetaDescription(Event $event): string
    {
        return $event->translation?->metaDescription
            ?? mb_substr(strip_tags($event->getTeaser()), 0, 160);
    }

    public function getCanonicalUrl(Event $event, string $languageIso = 'ger'): string
    {
        return $this->getEventUrl($event, $languageIso);
    }
}
