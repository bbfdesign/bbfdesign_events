<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Enum;

enum EventDateType: string
{
    case SINGLE = 'single';
    case MULTIDAY = 'multiday';
    case ALLDAY = 'allday';
    case TIMED = 'timed';

    public function label(): string
    {
        return match ($this) {
            self::SINGLE => 'Einzeltermin',
            self::MULTIDAY => 'Mehrtägig',
            self::ALLDAY => 'Ganztägig',
            self::TIMED => 'Mit Uhrzeiten',
        };
    }
}
