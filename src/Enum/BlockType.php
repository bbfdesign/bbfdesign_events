<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Enum;

enum BlockType: string
{
    case HERO = 'hero';
    case TEXT = 'text';
    case IMAGE = 'image';
    case GALLERY = 'gallery';
    case VIDEO = 'video';
    case PROGRAM = 'program';
    case PARTNERS = 'partners';
    case KNOWLEDGE = 'knowledge';
    case MAP = 'map';
    case CTA = 'cta';
    case TICKETS = 'tickets';
    case FAQ = 'faq';
    case TEASER = 'teaser';
    case LINKS = 'links';
    case HTML = 'html';

    public function label(): string
    {
        return match ($this) {
            self::HERO => 'Hero',
            self::TEXT => 'Textblock',
            self::IMAGE => 'Bild',
            self::GALLERY => 'Galerie',
            self::VIDEO => 'Video',
            self::PROGRAM => 'Programm',
            self::PARTNERS => 'Partner',
            self::KNOWLEDGE => 'Wissenswertes',
            self::MAP => 'Karte',
            self::CTA => 'Call-to-Action',
            self::TICKETS => 'Tickets',
            self::FAQ => 'FAQ',
            self::TEASER => 'Teaserliste',
            self::LINKS => 'Links',
            self::HTML => 'HTML (frei)',
        };
    }

    public function isDynamic(): bool
    {
        return in_array($this, [
            self::PROGRAM,
            self::PARTNERS,
            self::KNOWLEDGE,
            self::MAP,
            self::TICKETS,
            self::TEASER,
        ], true);
    }
}
