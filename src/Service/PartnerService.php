<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Service;

use JTL\DB\DbInterface;
use Plugin\bbfdesign_events\src\Config\EventConfig;
use Plugin\bbfdesign_events\src\Model\Partner\Partner;
use Plugin\bbfdesign_events\src\Model\Partner\PartnerCategory;
use Plugin\bbfdesign_events\src\Model\Partner\PartnerCategoryTranslation;
use Plugin\bbfdesign_events\src\Model\Partner\PartnerTranslation;

class PartnerService
{
    public function __construct(
        private readonly DbInterface $db
    ) {}

    /**
     * @return Partner[]
     */
    public function getPartnersForEvent(int $eventId, string $languageIso): array
    {
        $rows = $this->db->getObjects(
            'SELECT p.*, pt.name, pt.short_desc, pt.long_desc, pt.cta_label, pt.cta_url,
                    epm.category_id as event_cat_id, epm.sort_order as event_sort,
                    pc.slug as pcat_slug, pct.name as pcat_name
             FROM bbf_event_partner_mapping epm
             JOIN bbf_partners p ON epm.partner_id = p.id
             LEFT JOIN bbf_partners_translation pt ON p.id = pt.partner_id AND pt.language_iso = :lang
             LEFT JOIN bbf_partner_categories pc ON epm.category_id = pc.id
             LEFT JOIN bbf_partner_categories_translation pct ON pc.id = pct.category_id AND pct.language_iso = :lang
             WHERE epm.event_id = :eid AND epm.is_visible = 1 AND p.is_active = 1
             ORDER BY epm.sort_order, p.sort_order',
            ['eid' => $eventId, 'lang' => $languageIso]
        );

        $partners = [];
        foreach ($rows as $row) {
            $partner = new Partner();
            $partner->id = (int) $row->id;
            $partner->slug = $row->slug;
            $partner->logo = $row->logo;
            $partner->logoDark = $row->logo_dark;
            $partner->websiteUrl = $row->website_url;
            $partner->isActive = (bool) $row->is_active;
            $partner->sortOrder = (int) $row->event_sort;

            if ($row->name !== null) {
                $t = new PartnerTranslation();
                $t->partnerId = $partner->id;
                $t->languageIso = $languageIso;
                $t->name = $row->name;
                $t->shortDesc = $row->short_desc;
                $t->longDesc = $row->long_desc;
                $t->ctaLabel = $row->cta_label;
                $t->ctaUrl = $row->cta_url;
                $partner->translation = $t;
            }

            if ($row->pcat_slug !== null) {
                $cat = new PartnerCategory();
                $cat->id = (int) $row->event_cat_id;
                $cat->slug = $row->pcat_slug;
                if ($row->pcat_name !== null) {
                    $ct = new PartnerCategoryTranslation();
                    $ct->categoryId = $cat->id;
                    $ct->languageIso = $languageIso;
                    $ct->name = $row->pcat_name;
                    $cat->translation = $ct;
                }
                $partner->categories = [$cat];
            }

            $partners[] = $partner;
        }

        return $partners;
    }

    /**
     * @return Partner[]
     */
    public function getAllPartners(string $languageIso, bool $activeOnly = true): array
    {
        $where = $activeOnly ? 'WHERE p.is_active = 1' : '';
        $rows = $this->db->getObjects(
            "SELECT p.*, pt.name, pt.short_desc, pt.long_desc, pt.cta_label, pt.cta_url
             FROM bbf_partners p
             LEFT JOIN bbf_partners_translation pt ON p.id = pt.partner_id AND pt.language_iso = :lang
             {$where}
             ORDER BY p.sort_order, p.id",
            ['lang' => $languageIso]
        );

        $partners = [];
        foreach ($rows as $row) {
            $partner = new Partner();
            $partner->id = (int) $row->id;
            $partner->slug = $row->slug;
            $partner->logo = $row->logo;
            $partner->logoDark = $row->logo_dark;
            $partner->websiteUrl = $row->website_url;
            $partner->isActive = (bool) $row->is_active;
            $partner->sortOrder = (int) $row->sort_order;

            if ($row->name !== null) {
                $t = new PartnerTranslation();
                $t->partnerId = $partner->id;
                $t->languageIso = $languageIso;
                $t->name = $row->name;
                $t->shortDesc = $row->short_desc;
                $t->longDesc = $row->long_desc;
                $t->ctaLabel = $row->cta_label;
                $t->ctaUrl = $row->cta_url;
                $partner->translation = $t;
            }

            $partners[] = $partner;
        }

        return $partners;
    }
}
