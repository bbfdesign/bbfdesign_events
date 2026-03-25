<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Model\Program;

class ProgramCategoryTranslation
{
    public int $id = 0;
    public int $categoryId = 0;
    public string $languageIso = '';
    public string $name = '';
}
