<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Model\Area;

class AreaMarkerTranslation
{
    public int $id = 0;
    public int $markerId = 0;
    public string $languageIso = '';
    public string $title = '';
    public ?string $description = null;
}
