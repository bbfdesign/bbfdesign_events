<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Model;

use Plugin\bbfdesign_events\src\Enum\MediaType;

class EventMedia
{
    public int $id = 0;
    public int $eventId = 0;
    public MediaType $mediaType = MediaType::IMAGE;
    public ?string $filePath = null;
    public ?string $externalUrl = null;
    public ?string $altText = null;
    public ?string $title = null;
    public ?string $mimeType = null;
    public ?int $fileSize = null;
    public int $sortOrder = 0;
    public string $context = 'default';

    public function getUrl(): string
    {
        if ($this->mediaType->isExternal()) {
            return $this->externalUrl ?? '';
        }
        return $this->filePath ?? '';
    }

    public function isImage(): bool
    {
        return $this->mediaType === MediaType::IMAGE || $this->mediaType === MediaType::GALLERY;
    }

    public function isVideo(): bool
    {
        return $this->mediaType->isVideo();
    }
}
