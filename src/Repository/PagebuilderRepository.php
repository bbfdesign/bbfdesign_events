<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Repository;

use JTL\DB\DbInterface;
use Plugin\bbfdesign_events\src\Helper\DateHelper;
use Plugin\bbfdesign_events\src\Model\Pagebuilder\EventPage;
use Plugin\bbfdesign_events\src\Model\Pagebuilder\EventPageTemplate;

class PagebuilderRepository
{
    public function __construct(
        private readonly DbInterface $db
    ) {}

    public function findByEventAndLanguage(int $eventId, string $languageIso): ?EventPage
    {
        $row = $this->db->getSingleObject(
            'SELECT * FROM bbf_event_pages WHERE event_id = :eid AND language_iso = :lang',
            ['eid' => $eventId, 'lang' => $languageIso]
        );

        if ($row === null) {
            return null;
        }

        return $this->hydratePage($row);
    }

    public function savePage(EventPage $page): int
    {
        $data = (object) [
            'event_id' => $page->eventId,
            'language_iso' => $page->languageIso,
            'gjs_data' => $page->gjsData,
            'html_rendered' => $page->htmlRendered,
            'css_rendered' => $page->cssRendered,
        ];

        if ($page->id > 0) {
            $this->db->update('bbf_event_pages', 'id', $page->id, $data);
            return $page->id;
        }

        // Upsert: try insert, on duplicate update
        $existing = $this->findByEventAndLanguage($page->eventId, $page->languageIso);
        if ($existing !== null) {
            $this->db->update('bbf_event_pages', 'id', $existing->id, $data);
            return $existing->id;
        }

        return $this->db->insert('bbf_event_pages', $data);
    }

    public function deletePage(int $eventId, string $languageIso): bool
    {
        return $this->db->executeQuery(
            'DELETE FROM bbf_event_pages WHERE event_id = :eid AND language_iso = :lang',
            ['eid' => $eventId, 'lang' => $languageIso]
        ) > 0;
    }

    /**
     * @return EventPageTemplate[]
     */
    public function findAllTemplates(): array
    {
        $rows = $this->db->getObjects(
            'SELECT * FROM bbf_event_page_templates ORDER BY is_default DESC, name ASC'
        );

        return array_map(fn($row) => $this->hydrateTemplate($row), $rows);
    }

    public function findTemplateById(int $id): ?EventPageTemplate
    {
        $row = $this->db->getSingleObject(
            'SELECT * FROM bbf_event_page_templates WHERE id = :id',
            ['id' => $id]
        );

        return $row !== null ? $this->hydrateTemplate($row) : null;
    }

    public function saveTemplate(EventPageTemplate $template): int
    {
        $data = (object) [
            'name' => $template->name,
            'description' => $template->description,
            'gjs_data' => $template->gjsData,
            'thumbnail' => $template->thumbnail,
            'is_default' => $template->isDefault ? 1 : 0,
        ];

        if ($template->id > 0) {
            $this->db->update('bbf_event_page_templates', 'id', $template->id, $data);
            return $template->id;
        }

        return $this->db->insert('bbf_event_page_templates', $data);
    }

    public function deleteTemplate(int $id): bool
    {
        return $this->db->delete('bbf_event_page_templates', 'id', $id) > 0;
    }

    private function hydratePage(object $row): EventPage
    {
        $page = new EventPage();
        $page->id = (int) $row->id;
        $page->eventId = (int) $row->event_id;
        $page->languageIso = $row->language_iso;
        $page->gjsData = $row->gjs_data;
        $page->htmlRendered = $row->html_rendered;
        $page->cssRendered = $row->css_rendered;
        $page->updatedAt = DateHelper::parseDateTime($row->updated_at) ?? new \DateTimeImmutable();

        return $page;
    }

    private function hydrateTemplate(object $row): EventPageTemplate
    {
        $tpl = new EventPageTemplate();
        $tpl->id = (int) $row->id;
        $tpl->name = $row->name;
        $tpl->description = $row->description;
        $tpl->gjsData = $row->gjs_data;
        $tpl->thumbnail = $row->thumbnail;
        $tpl->isDefault = (bool) $row->is_default;
        $tpl->createdAt = DateHelper::parseDateTime($row->created_at) ?? new \DateTimeImmutable();
        $tpl->updatedAt = DateHelper::parseDateTime($row->updated_at) ?? new \DateTimeImmutable();

        return $tpl;
    }
}
