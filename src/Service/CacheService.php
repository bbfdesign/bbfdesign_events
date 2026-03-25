<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Service;

use JTL\Cache\JTLCacheInterface;
use Plugin\bbfdesign_events\src\Config\EventConfig;

class CacheService
{
    public function __construct(
        private readonly JTLCacheInterface $cache
    ) {}

    public function get(string $key): mixed
    {
        return $this->cache->get(EventConfig::CACHE_TAG_PREFIX . $key);
    }

    public function set(string $key, mixed $value, array $tags = [], int $ttl = 3600): void
    {
        $prefixedTags = array_map(
            fn(string $tag) => EventConfig::CACHE_TAG_PREFIX . $tag,
            $tags
        );
        $prefixedTags[] = 'bbf_events'; // Global tag

        $this->cache->set(
            EventConfig::CACHE_TAG_PREFIX . $key,
            $value,
            $prefixedTags,
            $ttl
        );
    }

    public function invalidateAll(): void
    {
        $this->cache->flushTags(['bbf_events']);
    }

    public function invalidateEvent(int $eventId): void
    {
        $this->cache->flushTags([EventConfig::CACHE_TAG_PREFIX . 'event_' . $eventId]);
    }

    public function invalidateListings(): void
    {
        $this->cache->flushTags([EventConfig::CACHE_TAG_PREFIX . 'listings']);
    }

    public function invalidatePartners(): void
    {
        $this->cache->flushTags([EventConfig::CACHE_TAG_PREFIX . 'partners']);
    }

    public function invalidateKnowledge(): void
    {
        $this->cache->flushTags([EventConfig::CACHE_TAG_PREFIX . 'knowledge']);
    }
}
