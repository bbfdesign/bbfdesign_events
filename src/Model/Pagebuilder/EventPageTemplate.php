<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Model\Pagebuilder;

class EventPageTemplate
{
    public int $id = 0;
    public string $name = '';
    public ?string $description = null;
    public string $gjsData = '';
    public ?string $thumbnail = null;
    public bool $isDefault = false;
    public \DateTimeImmutable $createdAt;
    public \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }
}
