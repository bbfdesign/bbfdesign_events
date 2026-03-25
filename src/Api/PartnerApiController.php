<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Api;

use JTL\Shop;
use Plugin\bbfdesign_events\src\Service\PartnerService;

class PartnerApiController
{
    private PartnerService $partnerService;

    public function __construct()
    {
        $this->partnerService = new PartnerService(Shop::Container()->getDB());
    }

    public function getAll(string $lang): array
    {
        $partners = $this->partnerService->getAllPartners($lang);
        return array_map(fn($p) => [
            'id' => $p->id,
            'slug' => $p->slug,
            'name' => $p->getName(),
            'logo' => $p->logo,
            'website_url' => $p->websiteUrl,
            'short_desc' => $p->getShortDesc(),
        ], $partners);
    }

    public function getByEvent(int $eventId, string $lang): array
    {
        $partners = $this->partnerService->getPartnersForEvent($eventId, $lang);
        return array_map(fn($p) => [
            'id' => $p->id,
            'name' => $p->getName(),
            'logo' => $p->logo,
            'website_url' => $p->websiteUrl,
        ], $partners);
    }
}
