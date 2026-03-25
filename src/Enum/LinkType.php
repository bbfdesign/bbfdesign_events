<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Enum;

enum LinkType: string
{
    case INTERNAL = 'internal';
    case EXTERNAL = 'external';
    case PRODUCT = 'product';
    case CATEGORY = 'category';
    case CMS = 'cms';
    case EVENT = 'event';
    case PLUGIN = 'plugin';

    public function label(): string
    {
        return match ($this) {
            self::INTERNAL => 'Interner Link',
            self::EXTERNAL => 'Externer Link',
            self::PRODUCT => 'Produkt',
            self::CATEGORY => 'Kategorie',
            self::CMS => 'CMS-Seite',
            self::EVENT => 'Event',
            self::PLUGIN => 'Plugin-Seite',
        };
    }

    public function isExternal(): bool
    {
        return $this === self::EXTERNAL;
    }
}
