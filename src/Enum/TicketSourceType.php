<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Enum;

enum TicketSourceType: string
{
    case WAWI_ARTICLE = 'wawi_article';
    case EXTERNAL = 'external';
    case PLUGIN_NATIVE = 'plugin_native';

    public function label(): string
    {
        return match ($this) {
            self::WAWI_ARTICLE => 'Wawi-Artikel',
            self::EXTERNAL => 'Externer Link',
            self::PLUGIN_NATIVE => 'Plugin-Ticket (intern)',
        };
    }

    public function isInternal(): bool
    {
        return $this !== self::EXTERNAL;
    }
}
