<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Model\Area;

class AreaMap
{
    public int $id = 0;
    public string $slug = '';
    public string $mapType = 'interactive';
    public ?string $staticImage = null;
    public ?float $centerLat = null;
    public ?float $centerLng = null;
    public int $zoomLevel = 14;
    public bool $isActive = true;

    /** @var AreaMapTranslation[] */
    public array $translations = [];

    public ?AreaMapTranslation $translation = null;

    /** @var AreaMarkerGroup[] */
    public array $markerGroups = [];

    /** @var AreaMarker[] */
    public array $markers = [];

    public function getTitle(): string
    {
        return $this->translation?->title ?? '';
    }

    public function getDescription(): string
    {
        return $this->translation?->description ?? '';
    }

    public function isInteractive(): bool
    {
        return $this->mapType === 'interactive';
    }

    public function isStaticImage(): bool
    {
        return $this->mapType === 'static_image';
    }
}
