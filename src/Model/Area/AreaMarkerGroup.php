<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Model\Area;

class AreaMarkerGroup
{
    public int $id = 0;
    public int $mapId = 0;
    public string $color = '#EF4444';
    public ?string $icon = null;
    public int $sortOrder = 0;

    /** @var AreaMarkerGroupTranslation[] */
    public array $translations = [];

    public ?AreaMarkerGroupTranslation $translation = null;

    public function getName(): string
    {
        return $this->translation?->name ?? '';
    }
}
