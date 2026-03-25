<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Helper;

use Plugin\bbfdesign_events\src\Model\EventDate;

class DateHelper
{
    private const MONTHS_DE = [
        1 => 'Januar', 2 => 'Februar', 3 => 'März', 4 => 'April',
        5 => 'Mai', 6 => 'Juni', 7 => 'Juli', 8 => 'August',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Dezember',
    ];

    private const MONTHS_DE_SHORT = [
        1 => 'Jan', 2 => 'Feb', 3 => 'Mär', 4 => 'Apr',
        5 => 'Mai', 6 => 'Jun', 7 => 'Jul', 8 => 'Aug',
        9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Dez',
    ];

    private const WEEKDAYS_DE = [
        1 => 'Montag', 2 => 'Dienstag', 3 => 'Mittwoch', 4 => 'Donnerstag',
        5 => 'Freitag', 6 => 'Samstag', 7 => 'Sonntag',
    ];

    public static function formatDate(\DateTimeImmutable $date, string $format = 'long'): string
    {
        return match ($format) {
            'short' => $date->format('d.m.Y'),
            'medium' => $date->format('d.') . ' '
                . self::MONTHS_DE_SHORT[(int) $date->format('n')]
                . ' ' . $date->format('Y'),
            'long' => $date->format('d.') . ' '
                . self::MONTHS_DE[(int) $date->format('n')]
                . ' ' . $date->format('Y'),
            'full' => self::WEEKDAYS_DE[(int) $date->format('N')] . ', '
                . $date->format('d.') . ' '
                . self::MONTHS_DE[(int) $date->format('n')]
                . ' ' . $date->format('Y'),
            default => $date->format('d.m.Y'),
        };
    }

    public static function formatDateRange(EventDate $eventDate, string $format = 'long'): string
    {
        $start = self::formatDate($eventDate->dateStart, $format);
        if ($eventDate->dateEnd === null || $eventDate->isSingleDay()) {
            return $start;
        }
        $end = self::formatDate($eventDate->dateEnd, $format);
        return $start . ' – ' . $end;
    }

    public static function formatTime(\DateTimeImmutable $time): string
    {
        return $time->format('H:i') . ' Uhr';
    }

    public static function toIso8601Date(\DateTimeImmutable $date): string
    {
        return $date->format('Y-m-d');
    }

    public static function toIso8601DateTime(\DateTimeImmutable $dateTime): string
    {
        return $dateTime->format('Y-m-d\TH:i:sP');
    }

    public static function parseDate(?string $value): ?\DateTimeImmutable
    {
        if ($value === null || $value === '') {
            return null;
        }
        $parsed = \DateTimeImmutable::createFromFormat('Y-m-d', $value);
        return $parsed !== false ? $parsed->setTime(0, 0) : null;
    }

    public static function parseDateTime(?string $value): ?\DateTimeImmutable
    {
        if ($value === null || $value === '') {
            return null;
        }
        $parsed = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $value);
        return $parsed !== false ? $parsed : null;
    }

    public static function parseTime(?string $value): ?\DateTimeImmutable
    {
        if ($value === null || $value === '') {
            return null;
        }
        $parsed = \DateTimeImmutable::createFromFormat('H:i:s', $value);
        if ($parsed === false) {
            $parsed = \DateTimeImmutable::createFromFormat('H:i', $value);
        }
        return $parsed !== false ? $parsed : null;
    }
}
