<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Model\Area;

class AreaMapTranslation
{
    public int $id = 0;
    public int $mapId = 0;
    public string $languageIso = '';
    public string $title = '';
    public ?string $description = null;
}
