<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Config;

class EventConfig
{
    public const PLUGIN_ID = 'bbfdesign_events';
    public const TABLE_PREFIX = 'bbf_';
    public const BASE_PATH = 'veranstaltungen';
    public const MEDIA_BASE_DIR = 'mediafiles/bbfdesign_events/';
    public const DEFAULT_LANGUAGE = 'ger';

    public const ITEMS_PER_PAGE = 12;
    public const MAX_ITEMS_PER_PAGE = 48;

    public const CACHE_TTL_LISTING = 3600;
    public const CACHE_TTL_DETAIL = 7200;
    public const CACHE_TTL_PARTNERS = 86400;
    public const CACHE_TTL_KNOWLEDGE = 86400;
    public const CACHE_TAG_PREFIX = 'bbf_events_';

    public const MEDIA_DIRS = [
        'images' => 'images/',
        'gallery' => 'gallery/',
        'videos' => 'videos/',
        'downloads' => 'downloads/',
        'partners' => 'partners/',
    ];

    public const ALLOWED_UPLOAD_TYPES = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        'image/svg+xml',
        'video/mp4',
        'video/webm',
        'application/pdf',
    ];

    public const MAX_UPLOAD_SIZE = 20 * 1024 * 1024; // 20 MB

    public static function getMediaPath(string $context = 'images'): string
    {
        return self::MEDIA_BASE_DIR . (self::MEDIA_DIRS[$context] ?? 'images/');
    }

    public static function getAbsoluteMediaPath(string $context = 'images'): string
    {
        return \PFAD_ROOT . self::getMediaPath($context);
    }

    public static function getPluginPath(): string
    {
        return \PFAD_ROOT . 'plugins/' . self::PLUGIN_ID . '/';
    }

    public static function getFrontendCssPath(): string
    {
        return 'plugins/' . self::PLUGIN_ID . '/frontend/css/';
    }

    public static function getFrontendJsPath(): string
    {
        return 'plugins/' . self::PLUGIN_ID . '/frontend/js/';
    }
}
