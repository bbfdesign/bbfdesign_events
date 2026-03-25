<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Model\Knowledge;

class KnowledgeItemTranslation
{
    public int $id = 0;
    public int $itemId = 0;
    public string $languageIso = '';
    public string $title = '';
    public ?string $teaser = null;
    public ?string $content = null;
    public ?string $ctaLabel = null;
    public ?string $ctaUrl = null;
}
