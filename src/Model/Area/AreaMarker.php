<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Model\Area;

class AreaMarker
{
    public int $id = 0;
    public int $mapId = 0;
    public ?int $groupId = null;
    public ?float $lat = null;
    public ?float $lng = null;
    public ?float $posX = null;
    public ?float $posY = null;
    public ?string $image = null;
    public int $sortOrder = 0;

    /** @var AreaMarkerTranslation[] */
    public array $translations = [];

    public ?AreaMarkerTranslation $translation = null;

    public ?AreaMarkerGroup $group = null;

    public function getTitle(): string
    {
        return $this->translation?->title ?? '';
    }

    public function getDescription(): string
    {
        return $this->translation?->description ?? '';
    }

    public function hasGeoCoordinates(): bool
    {
        return $this->lat !== null && $this->lng !== null;
    }

    public function hasImagePosition(): bool
    {
        return $this->posX !== null && $this->posY !== null;
    }
}
