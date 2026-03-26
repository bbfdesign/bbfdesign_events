<?php
// test_insert.php – Einmalig ausführen um Testdaten anzulegen, danach löschen
require_once __DIR__ . '/../../includes/globalinclude.php';

$db = JTL\Shop::Container()->getDB();

$exists = $db->getSingleObject("SELECT id FROM bbf_events WHERE slug = 'test-veranstaltung'");
if ($exists) {
    echo "Test-Event existiert bereits (ID: {$exists->id})\n";
    exit;
}

$eventId = $db->insert('bbf_events', (object) [
    'status'      => 'published',
    'slug'        => 'test-veranstaltung',
    'event_type'  => 'single',
    'is_featured' => 1,
    'sort_order'  => 0,
]);

$db->insert('bbf_events_translation', (object) [
    'event_id'     => $eventId,
    'language_iso' => 'ger',
    'title'        => 'Test-Veranstaltung',
    'subtitle'     => 'Ein Testevent',
    'teaser'       => 'Das ist ein Teaser für das Testevent.',
    'description'  => '<p>Beschreibung des Test-Events.</p>',
]);

$db->insert('bbf_event_dates', (object) [
    'event_id'   => $eventId,
    'date_start' => '2026-06-15',
    'date_end'   => '2026-06-15',
    'is_allday'  => 1,
    'sort_order' => 0,
]);

echo "Test-Event eingefügt mit ID: {$eventId}\n";
