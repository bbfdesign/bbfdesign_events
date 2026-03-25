<?php

declare(strict_types=1);

namespace Plugin\bbfdesign_events\src\Migration;

use JTL\DB\DbInterface;

class Migration20260101000000
{
    private DbInterface $db;

    public function __construct(DbInterface $db, string $pluginId = '')
    {
        $this->db = $db;
    }

    private function execute(string $sql): void
    {
        $this->db->executeQuery($sql);
    }

    public function up(): void
    {
        $this->createCoreTables();
        $this->createProgramTables();
        $this->createPartnerTables();
        $this->createKnowledgeTables();
        $this->createAreaTables();
        $this->createTicketTables();
        $this->createPagebuilderTables();
    }

    public function down(): void
    {
        $tables = [
            'bbf_event_page_templates',
            'bbf_event_pages',
            'bbf_event_tickets_translation',
            'bbf_event_tickets',
            'bbf_ticket_categories_translation',
            'bbf_ticket_categories',
            'bbf_event_area_mapping',
            'bbf_area_markers_translation',
            'bbf_area_markers',
            'bbf_area_marker_groups_translation',
            'bbf_area_marker_groups',
            'bbf_area_maps_translation',
            'bbf_area_maps',
            'bbf_event_knowledge_mapping',
            'bbf_knowledge_category_mapping',
            'bbf_knowledge_categories_translation',
            'bbf_knowledge_categories',
            'bbf_knowledge_items_translation',
            'bbf_knowledge_items',
            'bbf_event_partner_mapping',
            'bbf_partner_category_mapping',
            'bbf_partner_categories_translation',
            'bbf_partner_categories',
            'bbf_partners_translation',
            'bbf_partners',
            'bbf_event_program_entries_translation',
            'bbf_event_program_entries',
            'bbf_event_program_categories_translation',
            'bbf_event_program_categories',
            'bbf_event_links_translation',
            'bbf_event_links',
            'bbf_event_media',
            'bbf_event_category_mapping',
            'bbf_event_categories_translation',
            'bbf_event_categories',
            'bbf_event_timeslots',
            'bbf_event_dates',
            'bbf_events_translation',
            'bbf_events',
        ];

        foreach ($tables as $table) {
            $this->execute('DROP TABLE IF EXISTS ' . $table);
        }
    }

    private function createCoreTables(): void
    {
        $this->execute("
            CREATE TABLE IF NOT EXISTS bbf_events (
                id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                status          ENUM('draft','scheduled','published','archived') NOT NULL DEFAULT 'draft',
                slug            VARCHAR(255) NOT NULL,
                hero_image      VARCHAR(512) DEFAULT NULL,
                event_type      ENUM('single','multiday','allday','timed') NOT NULL DEFAULT 'single',
                is_featured     TINYINT(1) NOT NULL DEFAULT 0,
                sort_order      INT NOT NULL DEFAULT 0,
                publish_from    DATETIME DEFAULT NULL,
                publish_to      DATETIME DEFAULT NULL,
                created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                created_by      INT DEFAULT NULL,
                UNIQUE KEY idx_slug (slug),
                KEY idx_status (status),
                KEY idx_featured (is_featured),
                KEY idx_publish (publish_from, publish_to)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $this->execute("
            CREATE TABLE IF NOT EXISTS bbf_events_translation (
                id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                event_id        INT UNSIGNED NOT NULL,
                language_iso    VARCHAR(5) NOT NULL,
                title           VARCHAR(512) NOT NULL,
                subtitle        VARCHAR(512) DEFAULT NULL,
                teaser          TEXT DEFAULT NULL,
                description     LONGTEXT DEFAULT NULL,
                slug_localized  VARCHAR(255) DEFAULT NULL,
                meta_title      VARCHAR(255) DEFAULT NULL,
                meta_description VARCHAR(512) DEFAULT NULL,
                og_title        VARCHAR(255) DEFAULT NULL,
                og_description  VARCHAR(512) DEFAULT NULL,
                og_image        VARCHAR(512) DEFAULT NULL,
                UNIQUE KEY idx_event_lang (event_id, language_iso),
                FOREIGN KEY (event_id) REFERENCES bbf_events(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $this->execute("
            CREATE TABLE IF NOT EXISTS bbf_event_dates (
                id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                event_id        INT UNSIGNED NOT NULL,
                date_start      DATE NOT NULL,
                date_end        DATE DEFAULT NULL,
                is_allday       TINYINT(1) NOT NULL DEFAULT 1,
                sort_order      INT NOT NULL DEFAULT 0,
                FOREIGN KEY (event_id) REFERENCES bbf_events(id) ON DELETE CASCADE,
                KEY idx_event_dates (event_id, date_start)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $this->execute("
            CREATE TABLE IF NOT EXISTS bbf_event_timeslots (
                id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                event_date_id   INT UNSIGNED NOT NULL,
                time_start      TIME NOT NULL,
                time_end        TIME DEFAULT NULL,
                label           VARCHAR(255) DEFAULT NULL,
                sort_order      INT NOT NULL DEFAULT 0,
                FOREIGN KEY (event_date_id) REFERENCES bbf_event_dates(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $this->execute("
            CREATE TABLE IF NOT EXISTS bbf_event_categories (
                id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                slug            VARCHAR(255) NOT NULL,
                parent_id       INT UNSIGNED DEFAULT NULL,
                sort_order      INT NOT NULL DEFAULT 0,
                is_active       TINYINT(1) NOT NULL DEFAULT 1,
                image           VARCHAR(512) DEFAULT NULL,
                UNIQUE KEY idx_cat_slug (slug),
                FOREIGN KEY (parent_id) REFERENCES bbf_event_categories(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $this->execute("
            CREATE TABLE IF NOT EXISTS bbf_event_categories_translation (
                id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                category_id     INT UNSIGNED NOT NULL,
                language_iso    VARCHAR(5) NOT NULL,
                name            VARCHAR(255) NOT NULL,
                description     TEXT DEFAULT NULL,
                meta_title      VARCHAR(255) DEFAULT NULL,
                meta_description VARCHAR(512) DEFAULT NULL,
                UNIQUE KEY idx_cat_lang (category_id, language_iso),
                FOREIGN KEY (category_id) REFERENCES bbf_event_categories(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $this->execute("
            CREATE TABLE IF NOT EXISTS bbf_event_category_mapping (
                event_id        INT UNSIGNED NOT NULL,
                category_id     INT UNSIGNED NOT NULL,
                PRIMARY KEY (event_id, category_id),
                FOREIGN KEY (event_id) REFERENCES bbf_events(id) ON DELETE CASCADE,
                FOREIGN KEY (category_id) REFERENCES bbf_event_categories(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $this->execute("
            CREATE TABLE IF NOT EXISTS bbf_event_media (
                id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                event_id        INT UNSIGNED NOT NULL,
                media_type      ENUM('image','gallery','youtube','vimeo','local_video','download') NOT NULL,
                file_path       VARCHAR(512) DEFAULT NULL,
                external_url    VARCHAR(1024) DEFAULT NULL,
                alt_text        VARCHAR(512) DEFAULT NULL,
                title           VARCHAR(512) DEFAULT NULL,
                mime_type       VARCHAR(100) DEFAULT NULL,
                file_size       INT UNSIGNED DEFAULT NULL,
                sort_order      INT NOT NULL DEFAULT 0,
                context         VARCHAR(100) DEFAULT 'default',
                FOREIGN KEY (event_id) REFERENCES bbf_events(id) ON DELETE CASCADE,
                KEY idx_event_media (event_id, context)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $this->execute("
            CREATE TABLE IF NOT EXISTS bbf_event_links (
                id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                event_id        INT UNSIGNED NOT NULL,
                link_type       ENUM('internal','external','product','category','cms','event','plugin') NOT NULL,
                target_id       INT UNSIGNED DEFAULT NULL,
                target_url      VARCHAR(1024) DEFAULT NULL,
                target_plugin   VARCHAR(100) DEFAULT NULL,
                sort_order      INT NOT NULL DEFAULT 0,
                context         VARCHAR(100) DEFAULT 'related',
                FOREIGN KEY (event_id) REFERENCES bbf_events(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $this->execute("
            CREATE TABLE IF NOT EXISTS bbf_event_links_translation (
                id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                link_id         INT UNSIGNED NOT NULL,
                language_iso    VARCHAR(5) NOT NULL,
                label           VARCHAR(255) DEFAULT NULL,
                description     TEXT DEFAULT NULL,
                UNIQUE KEY idx_link_lang (link_id, language_iso),
                FOREIGN KEY (link_id) REFERENCES bbf_event_links(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    private function createProgramTables(): void
    {
        $this->execute("
            CREATE TABLE IF NOT EXISTS bbf_event_program_categories (
                id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                slug            VARCHAR(255) NOT NULL,
                color           VARCHAR(7) DEFAULT '#3B82F6',
                icon            VARCHAR(100) DEFAULT NULL,
                sort_order      INT NOT NULL DEFAULT 0,
                UNIQUE KEY idx_prog_cat_slug (slug)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $this->execute("
            CREATE TABLE IF NOT EXISTS bbf_event_program_categories_translation (
                id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                category_id     INT UNSIGNED NOT NULL,
                language_iso    VARCHAR(5) NOT NULL,
                name            VARCHAR(255) NOT NULL,
                UNIQUE KEY idx_prog_cat_lang (category_id, language_iso),
                FOREIGN KEY (category_id) REFERENCES bbf_event_program_categories(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $this->execute("
            CREATE TABLE IF NOT EXISTS bbf_event_program_entries (
                id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                event_id        INT UNSIGNED NOT NULL,
                event_date_id   INT UNSIGNED DEFAULT NULL,
                category_id     INT UNSIGNED DEFAULT NULL,
                time_start      TIME DEFAULT NULL,
                time_end        TIME DEFAULT NULL,
                speaker_name    VARCHAR(255) DEFAULT NULL,
                speaker_image   VARCHAR(512) DEFAULT NULL,
                link_url        VARCHAR(1024) DEFAULT NULL,
                link_target     VARCHAR(20) DEFAULT '_self',
                sort_order      INT NOT NULL DEFAULT 0,
                is_highlight    TINYINT(1) NOT NULL DEFAULT 0,
                FOREIGN KEY (event_id) REFERENCES bbf_events(id) ON DELETE CASCADE,
                FOREIGN KEY (event_date_id) REFERENCES bbf_event_dates(id) ON DELETE SET NULL,
                FOREIGN KEY (category_id) REFERENCES bbf_event_program_categories(id) ON DELETE SET NULL,
                KEY idx_program_event (event_id, sort_order)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $this->execute("
            CREATE TABLE IF NOT EXISTS bbf_event_program_entries_translation (
                id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                entry_id        INT UNSIGNED NOT NULL,
                language_iso    VARCHAR(5) NOT NULL,
                title           VARCHAR(512) NOT NULL,
                description     TEXT DEFAULT NULL,
                speaker_title   VARCHAR(255) DEFAULT NULL,
                UNIQUE KEY idx_prog_entry_lang (entry_id, language_iso),
                FOREIGN KEY (entry_id) REFERENCES bbf_event_program_entries(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    private function createPartnerTables(): void
    {
        $this->execute("
            CREATE TABLE IF NOT EXISTS bbf_partners (
                id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                slug            VARCHAR(255) NOT NULL,
                logo            VARCHAR(512) DEFAULT NULL,
                logo_dark       VARCHAR(512) DEFAULT NULL,
                website_url     VARCHAR(1024) DEFAULT NULL,
                is_active       TINYINT(1) NOT NULL DEFAULT 1,
                sort_order      INT NOT NULL DEFAULT 0,
                UNIQUE KEY idx_partner_slug (slug)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $this->execute("
            CREATE TABLE IF NOT EXISTS bbf_partners_translation (
                id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                partner_id      INT UNSIGNED NOT NULL,
                language_iso    VARCHAR(5) NOT NULL,
                name            VARCHAR(255) NOT NULL,
                short_desc      TEXT DEFAULT NULL,
                long_desc       LONGTEXT DEFAULT NULL,
                cta_label       VARCHAR(100) DEFAULT NULL,
                cta_url         VARCHAR(1024) DEFAULT NULL,
                UNIQUE KEY idx_partner_lang (partner_id, language_iso),
                FOREIGN KEY (partner_id) REFERENCES bbf_partners(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $this->execute("
            CREATE TABLE IF NOT EXISTS bbf_partner_categories (
                id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                slug            VARCHAR(255) NOT NULL,
                sort_order      INT NOT NULL DEFAULT 0,
                UNIQUE KEY idx_pcat_slug (slug)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $this->execute("
            CREATE TABLE IF NOT EXISTS bbf_partner_categories_translation (
                id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                category_id     INT UNSIGNED NOT NULL,
                language_iso    VARCHAR(5) NOT NULL,
                name            VARCHAR(255) NOT NULL,
                UNIQUE KEY idx_pcat_lang (category_id, language_iso),
                FOREIGN KEY (category_id) REFERENCES bbf_partner_categories(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $this->execute("
            CREATE TABLE IF NOT EXISTS bbf_partner_category_mapping (
                partner_id      INT UNSIGNED NOT NULL,
                category_id     INT UNSIGNED NOT NULL,
                PRIMARY KEY (partner_id, category_id),
                FOREIGN KEY (partner_id) REFERENCES bbf_partners(id) ON DELETE CASCADE,
                FOREIGN KEY (category_id) REFERENCES bbf_partner_categories(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $this->execute("
            CREATE TABLE IF NOT EXISTS bbf_event_partner_mapping (
                id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                event_id        INT UNSIGNED NOT NULL,
                partner_id      INT UNSIGNED NOT NULL,
                category_id     INT UNSIGNED DEFAULT NULL,
                sort_order      INT NOT NULL DEFAULT 0,
                is_visible      TINYINT(1) NOT NULL DEFAULT 1,
                UNIQUE KEY idx_event_partner (event_id, partner_id),
                FOREIGN KEY (event_id) REFERENCES bbf_events(id) ON DELETE CASCADE,
                FOREIGN KEY (partner_id) REFERENCES bbf_partners(id) ON DELETE CASCADE,
                FOREIGN KEY (category_id) REFERENCES bbf_partner_categories(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    private function createKnowledgeTables(): void
    {
        $this->execute("
            CREATE TABLE IF NOT EXISTS bbf_knowledge_items (
                id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                slug            VARCHAR(255) NOT NULL,
                image           VARCHAR(512) DEFAULT NULL,
                icon            VARCHAR(100) DEFAULT NULL,
                is_active       TINYINT(1) NOT NULL DEFAULT 1,
                sort_order      INT NOT NULL DEFAULT 0,
                UNIQUE KEY idx_know_slug (slug)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $this->execute("
            CREATE TABLE IF NOT EXISTS bbf_knowledge_items_translation (
                id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                item_id         INT UNSIGNED NOT NULL,
                language_iso    VARCHAR(5) NOT NULL,
                title           VARCHAR(512) NOT NULL,
                teaser          TEXT DEFAULT NULL,
                content         LONGTEXT DEFAULT NULL,
                cta_label       VARCHAR(100) DEFAULT NULL,
                cta_url         VARCHAR(1024) DEFAULT NULL,
                UNIQUE KEY idx_know_lang (item_id, language_iso),
                FOREIGN KEY (item_id) REFERENCES bbf_knowledge_items(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $this->execute("
            CREATE TABLE IF NOT EXISTS bbf_knowledge_categories (
                id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                slug            VARCHAR(255) NOT NULL,
                sort_order      INT NOT NULL DEFAULT 0,
                UNIQUE KEY idx_kcat_slug (slug)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $this->execute("
            CREATE TABLE IF NOT EXISTS bbf_knowledge_categories_translation (
                id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                category_id     INT UNSIGNED NOT NULL,
                language_iso    VARCHAR(5) NOT NULL,
                name            VARCHAR(255) NOT NULL,
                UNIQUE KEY idx_kcat_lang (category_id, language_iso),
                FOREIGN KEY (category_id) REFERENCES bbf_knowledge_categories(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $this->execute("
            CREATE TABLE IF NOT EXISTS bbf_knowledge_category_mapping (
                item_id         INT UNSIGNED NOT NULL,
                category_id     INT UNSIGNED NOT NULL,
                PRIMARY KEY (item_id, category_id),
                FOREIGN KEY (item_id) REFERENCES bbf_knowledge_items(id) ON DELETE CASCADE,
                FOREIGN KEY (category_id) REFERENCES bbf_knowledge_categories(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $this->execute("
            CREATE TABLE IF NOT EXISTS bbf_event_knowledge_mapping (
                id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                event_id        INT UNSIGNED NOT NULL,
                item_id         INT UNSIGNED NOT NULL,
                sort_order      INT NOT NULL DEFAULT 0,
                UNIQUE KEY idx_event_knowledge (event_id, item_id),
                FOREIGN KEY (event_id) REFERENCES bbf_events(id) ON DELETE CASCADE,
                FOREIGN KEY (item_id) REFERENCES bbf_knowledge_items(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    private function createAreaTables(): void
    {
        $this->execute("
            CREATE TABLE IF NOT EXISTS bbf_area_maps (
                id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                slug            VARCHAR(255) NOT NULL,
                map_type        ENUM('interactive','static_image','list') NOT NULL DEFAULT 'interactive',
                static_image    VARCHAR(512) DEFAULT NULL,
                center_lat      DECIMAL(10,7) DEFAULT NULL,
                center_lng      DECIMAL(10,7) DEFAULT NULL,
                zoom_level      TINYINT UNSIGNED DEFAULT 14,
                is_active       TINYINT(1) NOT NULL DEFAULT 1,
                UNIQUE KEY idx_area_slug (slug)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $this->execute("
            CREATE TABLE IF NOT EXISTS bbf_area_maps_translation (
                id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                map_id          INT UNSIGNED NOT NULL,
                language_iso    VARCHAR(5) NOT NULL,
                title           VARCHAR(255) NOT NULL,
                description     TEXT DEFAULT NULL,
                UNIQUE KEY idx_area_map_lang (map_id, language_iso),
                FOREIGN KEY (map_id) REFERENCES bbf_area_maps(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $this->execute("
            CREATE TABLE IF NOT EXISTS bbf_area_marker_groups (
                id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                map_id          INT UNSIGNED NOT NULL,
                color           VARCHAR(7) DEFAULT '#EF4444',
                icon            VARCHAR(100) DEFAULT NULL,
                sort_order      INT NOT NULL DEFAULT 0,
                FOREIGN KEY (map_id) REFERENCES bbf_area_maps(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $this->execute("
            CREATE TABLE IF NOT EXISTS bbf_area_marker_groups_translation (
                id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                group_id        INT UNSIGNED NOT NULL,
                language_iso    VARCHAR(5) NOT NULL,
                name            VARCHAR(255) NOT NULL,
                UNIQUE KEY idx_marker_grp_lang (group_id, language_iso),
                FOREIGN KEY (group_id) REFERENCES bbf_area_marker_groups(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $this->execute("
            CREATE TABLE IF NOT EXISTS bbf_area_markers (
                id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                map_id          INT UNSIGNED NOT NULL,
                group_id        INT UNSIGNED DEFAULT NULL,
                lat             DECIMAL(10,7) DEFAULT NULL,
                lng             DECIMAL(10,7) DEFAULT NULL,
                pos_x           DECIMAL(5,2) DEFAULT NULL,
                pos_y           DECIMAL(5,2) DEFAULT NULL,
                image           VARCHAR(512) DEFAULT NULL,
                sort_order      INT NOT NULL DEFAULT 0,
                FOREIGN KEY (map_id) REFERENCES bbf_area_maps(id) ON DELETE CASCADE,
                FOREIGN KEY (group_id) REFERENCES bbf_area_marker_groups(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $this->execute("
            CREATE TABLE IF NOT EXISTS bbf_area_markers_translation (
                id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                marker_id       INT UNSIGNED NOT NULL,
                language_iso    VARCHAR(5) NOT NULL,
                title           VARCHAR(255) NOT NULL,
                description     TEXT DEFAULT NULL,
                UNIQUE KEY idx_marker_lang (marker_id, language_iso),
                FOREIGN KEY (marker_id) REFERENCES bbf_area_markers(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $this->execute("
            CREATE TABLE IF NOT EXISTS bbf_event_area_mapping (
                event_id        INT UNSIGNED NOT NULL,
                map_id          INT UNSIGNED NOT NULL,
                sort_order      INT NOT NULL DEFAULT 0,
                PRIMARY KEY (event_id, map_id),
                FOREIGN KEY (event_id) REFERENCES bbf_events(id) ON DELETE CASCADE,
                FOREIGN KEY (map_id) REFERENCES bbf_area_maps(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    private function createTicketTables(): void
    {
        $this->execute("
            CREATE TABLE IF NOT EXISTS bbf_ticket_categories (
                id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                slug            VARCHAR(255) NOT NULL,
                color           VARCHAR(7) DEFAULT '#3B82F6',
                icon            VARCHAR(100) DEFAULT NULL,
                sort_order      INT NOT NULL DEFAULT 0,
                UNIQUE KEY idx_tcat_slug (slug)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $this->execute("
            CREATE TABLE IF NOT EXISTS bbf_ticket_categories_translation (
                id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                category_id     INT UNSIGNED NOT NULL,
                language_iso    VARCHAR(5) NOT NULL,
                name            VARCHAR(255) NOT NULL,
                description     TEXT DEFAULT NULL,
                UNIQUE KEY idx_tcat_lang (category_id, language_iso),
                FOREIGN KEY (category_id) REFERENCES bbf_ticket_categories(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $this->execute("
            CREATE TABLE IF NOT EXISTS bbf_event_tickets (
                id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                event_id        INT UNSIGNED NOT NULL,
                category_id     INT UNSIGNED DEFAULT NULL,
                source_type     ENUM('wawi_article','external','plugin_native') NOT NULL DEFAULT 'external',
                wawi_article_id INT UNSIGNED DEFAULT NULL,
                wawi_article_no VARCHAR(255) DEFAULT NULL,
                external_url    VARCHAR(1024) DEFAULT NULL,
                external_provider VARCHAR(255) DEFAULT NULL,
                price_net       DECIMAL(10,2) DEFAULT NULL,
                price_gross     DECIMAL(10,2) DEFAULT NULL,
                tax_rate        DECIMAL(5,2) DEFAULT NULL,
                currency        VARCHAR(3) DEFAULT 'EUR',
                max_quantity    INT UNSIGNED DEFAULT NULL,
                sold_quantity   INT UNSIGNED NOT NULL DEFAULT 0,
                available_from  DATETIME DEFAULT NULL,
                available_to    DATETIME DEFAULT NULL,
                is_active       TINYINT(1) NOT NULL DEFAULT 1,
                is_sold_out     TINYINT(1) NOT NULL DEFAULT 0,
                sort_order      INT NOT NULL DEFAULT 0,
                FOREIGN KEY (event_id) REFERENCES bbf_events(id) ON DELETE CASCADE,
                FOREIGN KEY (category_id) REFERENCES bbf_ticket_categories(id) ON DELETE SET NULL,
                KEY idx_event_tickets (event_id, is_active, sort_order),
                KEY idx_wawi_article (wawi_article_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $this->execute("
            CREATE TABLE IF NOT EXISTS bbf_event_tickets_translation (
                id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                ticket_id       INT UNSIGNED NOT NULL,
                language_iso    VARCHAR(5) NOT NULL,
                name            VARCHAR(255) NOT NULL,
                description     TEXT DEFAULT NULL,
                cta_label       VARCHAR(100) DEFAULT NULL,
                hint            VARCHAR(512) DEFAULT NULL,
                UNIQUE KEY idx_ticket_lang (ticket_id, language_iso),
                FOREIGN KEY (ticket_id) REFERENCES bbf_event_tickets(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    private function createPagebuilderTables(): void
    {
        $this->execute("
            CREATE TABLE IF NOT EXISTS bbf_event_pages (
                id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                event_id        INT UNSIGNED NOT NULL,
                language_iso    VARCHAR(5) NOT NULL,
                gjs_data        LONGTEXT DEFAULT NULL,
                html_rendered   LONGTEXT DEFAULT NULL,
                css_rendered    LONGTEXT DEFAULT NULL,
                updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY idx_event_page_lang (event_id, language_iso),
                FOREIGN KEY (event_id) REFERENCES bbf_events(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $this->execute("
            CREATE TABLE IF NOT EXISTS bbf_event_page_templates (
                id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name            VARCHAR(255) NOT NULL,
                description     TEXT DEFAULT NULL,
                gjs_data        LONGTEXT NOT NULL,
                thumbnail       VARCHAR(512) DEFAULT NULL,
                is_default      TINYINT(1) NOT NULL DEFAULT 0,
                created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }
}
