/**
 * BBF Events – Custom GrapesJS Block Types
 */

export function registerBbfBlocks(editor) {
    const bm = editor.BlockManager;

    // ── Struktur ──────────────────────────────────────────

    bm.add('bbf-section', {
        label: 'Sektion',
        category: 'Struktur',
        content: {
            tagName: 'section',
            classes: ['bbf-section'],
            droppable: true,
            attributes: { 'data-bbf-type': 'section' },
            components: [{ tagName: 'div', classes: ['container'], droppable: true }],
            styles: '.bbf-section { padding: 60px 0; }',
        },
        media: '<svg viewBox="0 0 24 24" width="40" height="40"><rect x="2" y="4" width="20" height="16" rx="2" fill="none" stroke="currentColor" stroke-width="1.5"/></svg>',
    });

    bm.add('bbf-columns-2', {
        label: '2 Spalten',
        category: 'Struktur',
        content: {
            tagName: 'div', classes: ['row'],
            components: [
                { tagName: 'div', classes: ['col-md-6'], droppable: true, content: '<p>Spalte 1</p>' },
                { tagName: 'div', classes: ['col-md-6'], droppable: true, content: '<p>Spalte 2</p>' },
            ],
        },
        media: '<svg viewBox="0 0 24 24" width="40" height="40"><rect x="2" y="4" width="9" height="16" rx="1" fill="none" stroke="currentColor" stroke-width="1.5"/><rect x="13" y="4" width="9" height="16" rx="1" fill="none" stroke="currentColor" stroke-width="1.5"/></svg>',
    });

    bm.add('bbf-columns-3', {
        label: '3 Spalten',
        category: 'Struktur',
        content: {
            tagName: 'div', classes: ['row'],
            components: [
                { tagName: 'div', classes: ['col-md-4'], droppable: true, content: '<p>Spalte 1</p>' },
                { tagName: 'div', classes: ['col-md-4'], droppable: true, content: '<p>Spalte 2</p>' },
                { tagName: 'div', classes: ['col-md-4'], droppable: true, content: '<p>Spalte 3</p>' },
            ],
        },
    });

    bm.add('bbf-columns-8-4', {
        label: '8 / 4 Spalten',
        category: 'Struktur',
        content: {
            tagName: 'div', classes: ['row'],
            components: [
                { tagName: 'div', classes: ['col-md-8'], droppable: true, content: '<p>Hauptbereich</p>' },
                { tagName: 'div', classes: ['col-md-4'], droppable: true, content: '<p>Sidebar</p>' },
            ],
        },
    });

    // ── Inhaltsblöcke ─────────────────────────────────────

    bm.add('bbf-hero', {
        label: 'Hero',
        category: 'Event-Inhalte',
        content: {
            tagName: 'div',
            classes: ['bbf-hero'],
            droppable: false,
            content: `
                <div class="bbf-hero__overlay"></div>
                <div class="bbf-hero__content">
                    <h1 class="bbf-hero__title" data-gjs-editable="true">Eventname</h1>
                    <p class="bbf-hero__subtitle" data-gjs-editable="true">Untertitel</p>
                    <a href="#" class="bbf-hero__cta btn btn-primary btn-lg">Jetzt Tickets sichern</a>
                </div>
            `,
            styles: `
                .bbf-hero { position: relative; min-height: 60vh; display: flex; align-items: center; justify-content: center; background-size: cover; background-position: center; color: #fff; text-align: center; }
                .bbf-hero__overlay { position: absolute; inset: 0; background: rgba(0,0,0,0.4); }
                .bbf-hero__content { position: relative; z-index: 1; }
                .bbf-hero__title { font-size: 3rem; font-weight: 800; }
            `,
        },
    });

    bm.add('bbf-text', {
        label: 'Textblock',
        category: 'Event-Inhalte',
        content: {
            tagName: 'div',
            classes: ['bbf-text-block'],
            content: `
                <h2 data-gjs-editable="true">Überschrift</h2>
                <div class="bbf-text-block__body" data-gjs-editable="true">
                    <p>Hier steht der Inhalt. Text kann direkt auf der Seite bearbeitet werden.</p>
                </div>
            `,
        },
    });

    bm.add('bbf-image', {
        label: 'Bild',
        category: 'Event-Inhalte',
        content: {
            tagName: 'div', classes: ['bbf-image-block'],
            content: '<img src="" alt="Bildbeschreibung" data-gjs-type="image" class="bbf-image-block__img img-fluid" />',
        },
    });

    bm.add('bbf-gallery', {
        label: 'Galerie',
        category: 'Event-Inhalte',
        content: {
            tagName: 'div', classes: ['bbf-gallery', 'row', 'g-3'], droppable: false,
            content: `
                <div class="col-md-4"><img src="" alt="" class="img-fluid" data-gjs-type="image" /></div>
                <div class="col-md-4"><img src="" alt="" class="img-fluid" data-gjs-type="image" /></div>
                <div class="col-md-4"><img src="" alt="" class="img-fluid" data-gjs-type="image" /></div>
            `,
        },
    });

    bm.add('bbf-video', {
        label: 'Video',
        category: 'Event-Inhalte',
        content: {
            tagName: 'div', classes: ['bbf-video-block'],
            content: '<div class="bbf-video-block__facade"><p>Video-Platzhalter – URL im Settings-Panel konfigurieren</p></div>',
            traits: [
                { type: 'select', name: 'video-source', label: 'Quelle',
                  options: [{ value: 'youtube', name: 'YouTube' }, { value: 'vimeo', name: 'Vimeo' }, { value: 'local', name: 'Lokal' }] },
                { type: 'text', name: 'video-url', label: 'Video URL' },
                { type: 'text', name: 'video-poster', label: 'Vorschaubild' },
                { type: 'checkbox', name: 'consent-required', label: 'Consent erforderlich' },
            ],
        },
    });

    bm.add('bbf-cta', {
        label: 'Call-to-Action',
        category: 'Event-Inhalte',
        content: {
            tagName: 'div', classes: ['bbf-cta-block'],
            content: `
                <div class="bbf-cta-block__inner text-center py-5">
                    <h2 data-gjs-editable="true">Jetzt dabei sein</h2>
                    <p data-gjs-editable="true">Sichere dir deinen Platz.</p>
                    <a href="#" class="btn btn-primary btn-lg" data-gjs-editable="true">Zum Ticketshop</a>
                </div>
            `,
        },
    });

    bm.add('bbf-faq', {
        label: 'FAQ / Akkordeon',
        category: 'Event-Inhalte',
        content: {
            tagName: 'div', classes: ['bbf-faq-block'],
            content: `
                <div class="accordion" id="bbf-faq-acc">
                    <div class="accordion-item">
                        <h3 class="accordion-header"><button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq-1">Frage 1?</button></h3>
                        <div id="faq-1" class="accordion-collapse collapse show" data-bs-parent="#bbf-faq-acc"><div class="accordion-body"><p>Antwort auf Frage 1.</p></div></div>
                    </div>
                    <div class="accordion-item">
                        <h3 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq-2">Frage 2?</button></h3>
                        <div id="faq-2" class="accordion-collapse collapse" data-bs-parent="#bbf-faq-acc"><div class="accordion-body"><p>Antwort auf Frage 2.</p></div></div>
                    </div>
                </div>
            `,
        },
    });

    bm.add('bbf-html', {
        label: 'Freier HTML-Block',
        category: 'Erweitert',
        content: {
            tagName: 'div', classes: ['bbf-html-custom'],
            content: '<p>Freier HTML-Inhalt – über den Code-Editor bearbeitbar</p>',
        },
    });

    // ── Dynamische Event-Blöcke ───────────────────────────

    const dynamicBlocks = [
        { id: 'bbf-program', label: 'Programm / Sessions', dynamic: 'program', icon: '📋',
          traits: [
              { type: 'select', name: 'display-mode', label: 'Darstellung', options: [{ value: 'timeline' }, { value: 'list' }, { value: 'grid' }], default: 'timeline' },
              { type: 'checkbox', name: 'group-by-day', label: 'Nach Tagen gruppieren' },
              { type: 'checkbox', name: 'show-speakers', label: 'Sprecher anzeigen' },
          ] },
        { id: 'bbf-partners', label: 'Partner / Sponsoren', dynamic: 'partners', icon: '🤝',
          traits: [
              { type: 'select', name: 'display-mode', label: 'Darstellung', options: [{ value: 'logo_grid' }, { value: 'cards' }, { value: 'slider' }], default: 'logo_grid' },
              { type: 'number', name: 'columns', label: 'Spalten', default: 4, min: 2, max: 6 },
              { type: 'checkbox', name: 'enable-modal', label: 'Detail-Modal' },
          ] },
        { id: 'bbf-tickets', label: 'Tickets', dynamic: 'tickets', icon: '🎫',
          traits: [
              { type: 'select', name: 'display-mode', label: 'Darstellung', options: [{ value: 'cards' }, { value: 'table' }, { value: 'compact' }], default: 'cards' },
              { type: 'checkbox', name: 'show-price', label: 'Preis anzeigen' },
              { type: 'checkbox', name: 'show-availability', label: 'Verfügbarkeit' },
          ] },
        { id: 'bbf-knowledge', label: 'Wissenswertes', dynamic: 'knowledge', icon: '💡',
          traits: [
              { type: 'select', name: 'display-mode', label: 'Darstellung', options: [{ value: 'cards' }, { value: 'accordion' }, { value: 'list' }], default: 'cards' },
              { type: 'number', name: 'columns', label: 'Spalten', default: 3, min: 1, max: 4 },
          ] },
        { id: 'bbf-area-map', label: 'Event-Area / Karte', dynamic: 'area_map', icon: '🗺️',
          traits: [
              { type: 'text', name: 'map-height', label: 'Kartenhöhe', default: '400px' },
              { type: 'checkbox', name: 'show-group-filter', label: 'Gruppen-Filter' },
              { type: 'checkbox', name: 'show-marker-list', label: 'Marker-Liste' },
          ] },
        { id: 'bbf-teaser-list', label: 'Teaserliste', dynamic: 'teaser_list', icon: '📰',
          traits: [
              { type: 'number', name: 'limit', label: 'Anzahl', default: 3, min: 1, max: 12 },
              { type: 'select', name: 'source', label: 'Quelle', options: [{ value: 'upcoming', name: 'Kommende' }, { value: 'same_category', name: 'Gleiche Kategorie' }, { value: 'featured', name: 'Empfohlen' }] },
          ] },
    ];

    dynamicBlocks.forEach(({ id, label, dynamic, icon, traits }) => {
        bm.add(id, {
            label,
            category: 'Event-Daten',
            content: {
                tagName: 'div',
                classes: [`bbf-${dynamic}-block`],
                attributes: { 'data-bbf-dynamic': dynamic },
                droppable: false,
                content: `<div class="bbf-dynamic-placeholder" style="padding:2rem;background:#f0f4f8;border:2px dashed #94a3b8;border-radius:0.5rem;text-align:center;">
                    <span style="font-size:2rem;display:block;margin-bottom:0.5rem;">${icon}</span>
                    <strong>${label}</strong><br>
                    <small style="color:#64748b;">Wird aus den Eventdaten generiert</small>
                </div>`,
                traits,
            },
        });
    });
}
