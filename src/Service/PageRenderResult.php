<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Service;

class PageRenderResult
{
    public function __construct(
        public readonly string $html,
        public readonly string $css
    ) {}

    public function hasContent(): bool
    {
        return $this->html !== '';
    }
}
