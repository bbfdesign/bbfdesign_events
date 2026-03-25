<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Model\Ticket;

use Plugin\bbfdesign_events\src\Enum\TicketSourceType;

class TicketOption
{
    public int $id = 0;
    public int $eventId = 0;
    public ?int $categoryId = null;
    public TicketSourceType $sourceType = TicketSourceType::EXTERNAL;

    // Wawi
    public ?int $wawiArticleId = null;
    public ?string $wawiArticleNo = null;

    // External
    public ?string $externalUrl = null;
    public ?string $externalProvider = null;

    // Plugin-native
    public ?float $priceNet = null;
    public ?float $priceGross = null;
    public ?float $taxRate = null;
    public string $currency = 'EUR';
    public ?int $maxQuantity = null;
    public int $soldQuantity = 0;

    // Availability
    public ?\DateTimeImmutable $availableFrom = null;
    public ?\DateTimeImmutable $availableTo = null;
    public bool $isActive = true;
    public bool $isSoldOut = false;

    public int $sortOrder = 0;

    /** @var TicketOptionTranslation[] */
    public array $translations = [];

    public ?TicketOptionTranslation $translation = null;

    public ?TicketCategory $category = null;

    // Resolved from Wawi
    public ?float $resolvedPrice = null;
    public ?bool $resolvedAvailable = null;
    public ?object $wawiArticle = null;
    public ?string $addToCartUrl = null;

    public function getName(): string
    {
        return $this->translation?->name ?? '';
    }

    public function getDescription(): string
    {
        return $this->translation?->description ?? '';
    }

    public function getCtaLabel(): string
    {
        return $this->translation?->ctaLabel ?? '';
    }

    public function getHint(): string
    {
        return $this->translation?->hint ?? '';
    }

    public function getDisplayPrice(): ?float
    {
        return $this->resolvedPrice ?? $this->priceGross;
    }

    public function isAvailable(): bool
    {
        if (!$this->isActive || $this->isSoldOut) {
            return false;
        }
        $now = new \DateTimeImmutable();
        if ($this->availableFrom !== null && $now < $this->availableFrom) {
            return false;
        }
        if ($this->availableTo !== null && $now > $this->availableTo) {
            return false;
        }
        if ($this->resolvedAvailable !== null) {
            return $this->resolvedAvailable;
        }
        return true;
    }

    public function isWawiArticle(): bool
    {
        return $this->sourceType === TicketSourceType::WAWI_ARTICLE;
    }

    public function isExternal(): bool
    {
        return $this->sourceType === TicketSourceType::EXTERNAL;
    }
}
