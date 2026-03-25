<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Enum;

enum MediaType: string
{
    case IMAGE = 'image';
    case GALLERY = 'gallery';
    case YOUTUBE = 'youtube';
    case VIMEO = 'vimeo';
    case LOCAL_VIDEO = 'local_video';
    case DOWNLOAD = 'download';

    public function label(): string
    {
        return match ($this) {
            self::IMAGE => 'Bild',
            self::GALLERY => 'Galerie',
            self::YOUTUBE => 'YouTube',
            self::VIMEO => 'Vimeo',
            self::LOCAL_VIDEO => 'Lokales Video',
            self::DOWNLOAD => 'Download',
        };
    }

    public function isVideo(): bool
    {
        return in_array($this, [self::YOUTUBE, self::VIMEO, self::LOCAL_VIDEO], true);
    }

    public function isExternal(): bool
    {
        return in_array($this, [self::YOUTUBE, self::VIMEO], true);
    }
}
