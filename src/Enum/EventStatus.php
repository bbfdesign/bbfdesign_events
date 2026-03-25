<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Enum;

enum EventStatus: string
{
    case DRAFT = 'draft';
    case SCHEDULED = 'scheduled';
    case PUBLISHED = 'published';
    case ARCHIVED = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Entwurf',
            self::SCHEDULED => 'Geplant',
            self::PUBLISHED => 'Veröffentlicht',
            self::ARCHIVED => 'Archiviert',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::DRAFT => 'badge-secondary',
            self::SCHEDULED => 'badge-info',
            self::PUBLISHED => 'badge-success',
            self::ARCHIVED => 'badge-warning',
        };
    }
}
