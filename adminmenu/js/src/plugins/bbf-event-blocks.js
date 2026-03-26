/**
 * BBF Events – Custom GrapesJS Event Block Types
 */

export default function bbfEventBlocks(editor) {
    const bm = editor.BlockManager;

    // ── Struktur ──────────────────────────────────────────

    bm.add('bbf-section', {
        label: 'Sektion',
        category: 'Struktur',
        attributes: { class: 'fa fa-square-o' },
        content: '<section class="bbf-section" style="padding:60px 0;"><div class="container"><p>Inhalt hier einfügen</p></div></section>',
    });

    bm.add('bbf-columns-2', {
        label: '2 Spalten',
        category: 'Struktur',
        attributes: { class: 'fa fa-columns' },
        content: '<div class="row"><div class="col-md-6"><p>Spalte 1</p></div><div class="col-md-6"><p>Spalte 2</p></div></div>',
    });

    bm.add('bbf-columns-3', {
        label: '3 Spalten',
        category: 'Struktur',
        attributes: { class: 'fa fa-th' },
        content: '<div class="row"><div class="col-md-4"><p>Spalte 1</p></div><div class="col-md-4"><p>Spalte 2</p></div><div class="col-md-4"><p>Spalte 3</p></div></div>',
    });

    bm.add('bbf-columns-8-4', {
        label: '8/4 Layout',
        category: 'Struktur',
        attributes: { class: 'fa fa-th-large' },
        content: '<div class="row"><div class="col-md-8"><p>Hauptbereich</p></div><div class="col-md-4"><p>Sidebar</p></div></div>',
    });

    // ── Event-Inhalte (statisch, inline editierbar) ───────

    bm.add('bbf-hero', {
        label: 'Hero',
        category: 'Event-Inhalte',
        attributes: { class: 'fa fa-image' },
        content: `<div class="bbf-hero" style="position:relative;min-height:60vh;display:flex;align-items:center;justify-content:center;background-size:cover;background-position:center;color:#fff;text-align:center;background-color:#1a1a2e;">
            <div style="position:absolute;inset:0;background:rgba(0,0,0,0.4);"></div>
            <div style="position:relative;z-index:1;">
                <h1 style="font-size:3rem;font-weight:800;">Eventname</h1>
                <p style="font-size:1.25rem;">Untertitel hier</p>
                <a href="#" class="btn btn-primary btn-lg">Jetzt Tickets sichern</a>
            </div>
        </div>`,
    });

    bm.add('bbf-text', {
        label: 'Textblock',
        category: 'Event-Inhalte',
        attributes: { class: 'fa fa-align-left' },
        content: '<div class="bbf-text-block"><h2>Überschrift</h2><p>Hier steht der Inhalt. Text kann direkt bearbeitet werden – mit Formatierung, Links und Medien.</p></div>',
    });

    bm.add('bbf-image', {
        label: 'Bild',
        category: 'Event-Inhalte',
        attributes: { class: 'fa fa-picture-o' },
        content: '<div class="bbf-image-block"><img src="https://placehold.co/800x400?text=Bild+hier" alt="Bildbeschreibung" class="img-fluid" style="border-radius:0.5rem;" /></div>',
    });

    bm.add('bbf-gallery', {
        label: 'Galerie',
        category: 'Event-Inhalte',
        attributes: { class: 'fa fa-th' },
        content: `<div class="row g-3">
            <div class="col-md-4"><img src="https://placehold.co/400x300?text=1" alt="" class="img-fluid" style="border-radius:0.5rem;" /></div>
            <div class="col-md-4"><img src="https://placehold.co/400x300?text=2" alt="" class="img-fluid" style="border-radius:0.5rem;" /></div>
            <div class="col-md-4"><img src="https://placehold.co/400x300?text=3" alt="" class="img-fluid" style="border-radius:0.5rem;" /></div>
        </div>`,
    });

    bm.add('bbf-video', {
        label: 'Video',
        category: 'Event-Inhalte',
        attributes: { class: 'fa fa-play-circle' },
        content: `<div class="bbf-video-block" style="padding:2rem;background:#f0f4f8;border:2px dashed #94a3b8;border-radius:0.5rem;text-align:center;">
            <span style="font-size:3rem;display:block;margin-bottom:0.5rem;">▶️</span>
            <strong>Video-Platzhalter</strong><br>
            <small style="color:#64748b;">Video-URL im Settings-Panel konfigurieren</small>
        </div>`,
    });

    bm.add('bbf-cta', {
        label: 'Call-to-Action',
        category: 'Event-Inhalte',
        attributes: { class: 'fa fa-bullhorn' },
        content: `<div class="text-center py-5" style="background:#f0f4f8;border-radius:0.5rem;">
            <h2>Jetzt dabei sein</h2>
            <p>Sichere dir deinen Platz bei diesem Event.</p>
            <a href="#" class="btn btn-primary btn-lg">Zum Ticketshop</a>
        </div>`,
    });

    bm.add('bbf-faq', {
        label: 'FAQ / Akkordeon',
        category: 'Event-Inhalte',
        attributes: { class: 'fa fa-question-circle' },
        content: `<div class="accordion" id="bbf-faq-pb">
            <div class="accordion-item">
                <h3 class="accordion-header"><button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq-pb-1">Frage 1?</button></h3>
                <div id="faq-pb-1" class="accordion-collapse collapse show" data-bs-parent="#bbf-faq-pb"><div class="accordion-body"><p>Antwort auf Frage 1.</p></div></div>
            </div>
            <div class="accordion-item">
                <h3 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq-pb-2">Frage 2?</button></h3>
                <div id="faq-pb-2" class="accordion-collapse collapse" data-bs-parent="#bbf-faq-pb"><div class="accordion-body"><p>Antwort auf Frage 2.</p></div></div>
            </div>
        </div>`,
    });

    bm.add('bbf-html', {
        label: 'Freies HTML',
        category: 'Event-Inhalte',
        attributes: { class: 'fa fa-code' },
        content: '<div class="bbf-html-custom"><p>Freier HTML-Inhalt – über den Code-Editor bearbeitbar</p></div>',
    });

    // ── Event-Daten (dynamisch, Platzhalter im Editor) ────

    const dynamicBlocks = [
        { id: 'program', label: 'Programm / Sessions', icon: '📋', fa: 'fa-list-alt' },
        { id: 'partners', label: 'Partner / Sponsoren', icon: '🤝', fa: 'fa-handshake-o' },
        { id: 'tickets', label: 'Tickets', icon: '🎫', fa: 'fa-ticket' },
        { id: 'knowledge', label: 'Wissenswertes', icon: '💡', fa: 'fa-lightbulb-o' },
        { id: 'area_map', label: 'Geländeplan / Karte', icon: '🗺️', fa: 'fa-map' },
        { id: 'teaser_list', label: 'Weitere Events', icon: '📰', fa: 'fa-newspaper-o' },
    ];

    dynamicBlocks.forEach(({ id, label, icon, fa }) => {
        bm.add('bbf-' + id, {
            label,
            category: 'Event-Daten',
            attributes: { class: 'fa ' + fa },
            content: `<div data-bbf-dynamic="${id}" style="padding:2rem;background:#f0f4f8;border:2px dashed #94a3b8;border-radius:0.5rem;text-align:center;">
                <span style="font-size:2rem;display:block;margin-bottom:0.5rem;">${icon}</span>
                <strong>${label}</strong><br>
                <small style="color:#64748b;">Wird aus den Eventdaten generiert</small>
            </div>`,
        });
    });
}
