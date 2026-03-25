<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Model\Knowledge;

class KnowledgeCategoryTranslation
{
    public int $id = 0;
    public int $categoryId = 0;
    public string $languageIso = '';
    public string $name = '';
}
