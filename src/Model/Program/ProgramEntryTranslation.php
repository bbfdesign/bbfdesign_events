<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Model\Program;

class ProgramEntryTranslation
{
    public int $id = 0;
    public int $entryId = 0;
    public string $languageIso = '';
    public string $title = '';
    public ?string $description = null;
    public ?string $speakerTitle = null;
}
