<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Helper;

class SlugHelper
{
    private const TRANSLITERATION = [
        'ä' => 'ae', 'ö' => 'oe', 'ü' => 'ue', 'ß' => 'ss',
        'Ä' => 'ae', 'Ö' => 'oe', 'Ü' => 'ue',
        'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
        'á' => 'a', 'à' => 'a', 'â' => 'a',
        'ó' => 'o', 'ò' => 'o', 'ô' => 'o',
        'ú' => 'u', 'ù' => 'u', 'û' => 'u',
        'í' => 'i', 'ì' => 'i', 'î' => 'i',
        'ñ' => 'n', 'ç' => 'c',
    ];

    public static function generate(string $text): string
    {
        $slug = strtr($text, self::TRANSLITERATION);
        $slug = mb_strtolower($slug);
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s-]+/', '-', $slug);
        return trim($slug, '-');
    }

    public static function ensureUnique(string $slug, callable $existsCheck): string
    {
        $original = $slug;
        $counter = 1;

        while ($existsCheck($slug)) {
            $counter++;
            $slug = $original . '-' . $counter;
        }

        return $slug;
    }
}
