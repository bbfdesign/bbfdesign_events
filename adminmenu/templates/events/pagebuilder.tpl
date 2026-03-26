<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BBF Events – Seiteneditor: {$event->getTitle()|escape:'html'}</title>

    {* GrapesJS CSS *}
    <link rel="stylesheet" href="https://unpkg.com/grapesjs@0.21.13/dist/css/grapes.min.css">

    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { overflow: hidden; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }

        /* ── Layout ─────────────────────────────────────── */
        .bbf-pb-wrapper { display: flex; flex-direction: column; height: 100vh; }

        .bbf-pb-topbar {
            display: flex; justify-content: space-between; align-items: center;
            padding: 0.5rem 1rem; background: #1a1a2e; color: #fff;
            border-bottom: 1px solid #333; z-index: 10; flex-shrink: 0;
        }
        .bbf-pb-topbar__title { font-size: 0.875rem; font-weight: 600; }
        .bbf-pb-topbar__event { font-size: 0.75rem; color: #94a3b8; margin-left: 0.5rem; }
        .bbf-pb-topbar__actions { display: flex; gap: 0.5rem; align-items: center; }
        .bbf-pb-topbar__actions select { background: #2a2a3e; color: #fff; border: 1px solid #444; border-radius: 4px; padding: 0.25rem 0.5rem; font-size: 0.8125rem; }

        .bbf-pb-btn {
            padding: 0.375rem 0.75rem; border: 1px solid #444; border-radius: 4px;
            background: #2a2a3e; color: #e2e8f0; cursor: pointer; font-size: 0.8125rem;
            transition: all 0.15s; display: inline-flex; align-items: center; gap: 0.375rem;
        }
        .bbf-pb-btn:hover { background: #3a3a5e; border-color: #666; }
        .bbf-pb-btn--primary { background: #2563EB; border-color: #2563EB; color: #fff; }
        .bbf-pb-btn--primary:hover { background: #1d4ed8; }
        .bbf-pb-btn--sm { padding: 0.25rem 0.5rem; font-size: 0.75rem; }

        .bbf-pb-main { display: flex; flex: 1; overflow: hidden; }

        /* ── Sidebar (Blocks) ──────────────────────────── */
        .bbf-pb-sidebar {
            width: 240px; background: #16162a; border-right: 1px solid #333;
            overflow-y: auto; flex-shrink: 0;
        }

        /* ── Canvas ────────────────────────────────────── */
        .bbf-pb-canvas { flex: 1; }
        .bbf-pb-canvas #bbf-pagebuilder { height: 100%; }

        /* ── Settings Panel ────────────────────────────── */
        .bbf-pb-settings {
            width: 280px; background: #16162a; border-left: 1px solid #333;
            overflow-y: auto; flex-shrink: 0;
        }

        /* ── GrapesJS Overrides ────────────────────────── */
        .gjs-one-bg { background-color: #16162a !important; }
        .gjs-two-color { color: #e2e8f0 !important; }
        .gjs-three-bg { background-color: #2a2a3e !important; }
        .gjs-four-color, .gjs-four-color-h:hover { color: #60a5fa !important; }
        .gjs-block { min-height: auto; padding: 0.5rem; }
        .gjs-block__media { height: auto; }

        /* Notification toast */
        .bbf-pb-toast {
            position: fixed; bottom: 1rem; right: 1rem; z-index: 10000;
            padding: 0.75rem 1.25rem; border-radius: 0.5rem;
            font-size: 0.875rem; font-weight: 500; color: #fff;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3); transition: opacity 0.3s;
        }
        .bbf-pb-toast--success { background: #059669; }
        .bbf-pb-toast--error { background: #DC2626; }
    </style>
</head>
<body>

<div class="bbf-pb-wrapper">
    {* ── Top Bar ─────────────────────────────────────── *}
    <div class="bbf-pb-topbar">
        <div>
            <span class="bbf-pb-topbar__title">BBF Events – Seiteneditor</span>
            <span class="bbf-pb-topbar__event">{$event->getTitle()|escape:'html'}</span>
        </div>
        <div class="bbf-pb-topbar__actions">
            {* Language selector *}
            <select id="bbf-pb-lang">
                {foreach $languages as $lang}
                    <option value="{$lang.iso}"{if $lang.iso === $currentLang} selected{/if}>{$lang.name}</option>
                {/foreach}
            </select>

            {* Device toggle *}
            <button class="bbf-pb-btn bbf-pb-btn--sm" id="bbf-pb-desktop" title="Desktop">🖥</button>
            <button class="bbf-pb-btn bbf-pb-btn--sm" id="bbf-pb-tablet" title="Tablet">📱</button>
            <button class="bbf-pb-btn bbf-pb-btn--sm" id="bbf-pb-mobile" title="Mobile">📲</button>

            <span style="width:1px;height:24px;background:#444;"></span>

            {* Actions *}
            <button class="bbf-pb-btn bbf-pb-btn--sm" id="bbf-pb-undo" title="Rückgängig">↶</button>
            <button class="bbf-pb-btn bbf-pb-btn--sm" id="bbf-pb-redo" title="Wiederholen">↷</button>
            <button class="bbf-pb-btn bbf-pb-btn--sm" id="bbf-pb-code" title="Code-Editor">&lt;/&gt;</button>
            <button class="bbf-pb-btn bbf-pb-btn--sm" id="bbf-pb-preview" title="Vorschau">👁</button>
            <button class="bbf-pb-btn bbf-pb-btn--sm" id="bbf-pb-fullscreen" title="Vollbild">⤢</button>

            <span style="width:1px;height:24px;background:#444;"></span>

            <button class="bbf-pb-btn bbf-pb-btn--primary" id="bbf-pb-save">
                💾 Speichern
            </button>
            <a href="{$backUrl}" class="bbf-pb-btn">✕ Schließen</a>
        </div>
    </div>

    {* ── Main Area ───────────────────────────────────── *}
    <div class="bbf-pb-main">
        {* Blocks Panel *}
        <div class="bbf-pb-sidebar" id="bbf-pb-blocks"></div>

        {* Canvas *}
        <div class="bbf-pb-canvas">
            <div id="bbf-pagebuilder"></div>
        </div>

        {* Settings Panel (Style Manager, Traits, Layers) *}
        <div class="bbf-pb-settings">
            <div id="bbf-pb-styles"></div>
            <div id="bbf-pb-traits"></div>
            <div id="bbf-pb-layers"></div>
        </div>
    </div>
</div>

{* ── GrapesJS + BBF Pagebuilder Bundle ──────────────── *}
<script src="https://unpkg.com/grapesjs@0.21.13/dist/grapes.min.js"></script>
<script src="https://unpkg.com/grapesjs-blocks-bootstrap5@0.2.31/dist/grapesjs-blocks-bootstrap5.min.js"></script>
<script src="https://unpkg.com/grapesjs-preset-webpage@1.0.3/dist/index.js"></script>

<script>
(function() {
    'use strict';

    const EVENT_ID = {$event->id};
    const CURRENT_LANG = '{$currentLang}';
    const AJAX_URL = '{$ajaxUrl}';
    const CSRF_TOKEN = '{$csrfToken}';

    // ── GrapesJS Init ─────────────────────────────────
    const editor = grapesjs.init({
        container: '#bbf-pagebuilder',
        fromElement: false,
        height: '100%',
        width: 'auto',
        storageManager: false,

        canvas: {
            styles: [
                'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',
                '{$ShopURL}/plugins/bbfdesign_events/frontend/css/bbf-events.css',
                '{$ShopURL}/plugins/bbfdesign_events/frontend/css/bbf-events-detail.css',
            ],
        },

        panels: { defaults: [] },

        blockManager: {
            appendTo: '#bbf-pb-blocks',
        },

        styleManager: {
            appendTo: '#bbf-pb-styles',
            sectors: [
                { name: 'Abmessungen', properties: ['width','min-width','max-width','height','min-height','padding','margin'] },
                { name: 'Typografie', properties: ['font-family','font-size','font-weight','letter-spacing','line-height','color','text-align','text-transform'] },
                { name: 'Hintergrund', properties: ['background-color','background-image','background-size','background-position'] },
                { name: 'Rahmen', properties: ['border','border-radius','box-shadow'] },
                { name: 'Extras', properties: ['opacity','display','position','overflow'] },
            ],
        },

        traitManager: {
            appendTo: '#bbf-pb-traits',
        },

        layerManager: {
            appendTo: '#bbf-pb-layers',
        },

        deviceManager: {
            devices: [
                { name: 'Desktop', width: '' },
                { name: 'Tablet', width: '768px', widthMedia: '992px' },
                { name: 'Mobile', width: '375px', widthMedia: '768px' },
            ],
        },

        plugins: ['grapesjs-blocks-bootstrap5', 'grapesjs-preset-webpage'],
        pluginsOpts: {
            'grapesjs-blocks-bootstrap5': {
                blocks: { container: true, row: true, column: true, column_break: true, alert: true, tabs: true, badge: true, card: true, card_container: true, collapse: true },
                blockCategories: { layout: 'Bootstrap Layout', components: 'Bootstrap Komponenten', typography: 'Typografie' },
            },
            'grapesjs-preset-webpage': {
                modalImportTitle: 'HTML importieren',
                modalImportButton: 'Importieren',
            },
        },
    });

    // ── Custom BBF Blocks ─────────────────────────────
    const bm = editor.BlockManager;

    bm.add('bbf-section', { label: 'Sektion', category: 'Struktur', content: '<section class="bbf-section" style="padding:60px 0;"><div class="container"><p>Inhalt hier</p></div></section>' });
    bm.add('bbf-columns-2', { label: '2 Spalten', category: 'Struktur', content: '<div class="row"><div class="col-md-6"><p>Spalte 1</p></div><div class="col-md-6"><p>Spalte 2</p></div></div>' });
    bm.add('bbf-columns-3', { label: '3 Spalten', category: 'Struktur', content: '<div class="row"><div class="col-md-4"><p>Spalte 1</p></div><div class="col-md-4"><p>Spalte 2</p></div><div class="col-md-4"><p>Spalte 3</p></div></div>' });
    bm.add('bbf-columns-8-4', { label: '8/4 Layout', category: 'Struktur', content: '<div class="row"><div class="col-md-8"><p>Hauptbereich</p></div><div class="col-md-4"><p>Sidebar</p></div></div>' });

    bm.add('bbf-hero', { label: 'Hero', category: 'Event-Inhalte', content: '<div class="bbf-hero" style="position:relative;min-height:60vh;display:flex;align-items:center;justify-content:center;background-size:cover;background-position:center;color:#fff;text-align:center;background-color:#1a1a2e;"><div style="position:absolute;inset:0;background:rgba(0,0,0,0.4);"></div><div style="position:relative;z-index:1;"><h1 style="font-size:3rem;font-weight:800;">Eventname</h1><p style="font-size:1.25rem;">Untertitel</p><a href="#" class="btn btn-primary btn-lg">Jetzt Tickets sichern</a></div></div>' });
    bm.add('bbf-text', { label: 'Textblock', category: 'Event-Inhalte', content: '<div class="bbf-text-block"><h2>Überschrift</h2><p>Hier steht der Inhalt. Text kann direkt auf der Seite bearbeitet werden.</p></div>' });
    bm.add('bbf-image', { label: 'Bild', category: 'Event-Inhalte', content: '<div class="bbf-image-block"><img src="https://placehold.co/800x400?text=Bild" alt="Bildbeschreibung" class="img-fluid" style="border-radius:0.5rem;" /></div>' });
    bm.add('bbf-cta', { label: 'Call-to-Action', category: 'Event-Inhalte', content: '<div class="text-center py-5" style="background:#f0f4f8;border-radius:0.5rem;"><h2>Jetzt dabei sein</h2><p>Sichere dir deinen Platz.</p><a href="#" class="btn btn-primary btn-lg">Zum Ticketshop</a></div>' });
    bm.add('bbf-faq', { label: 'FAQ', category: 'Event-Inhalte', content: '<div class="accordion" id="bbf-faq-pb"><div class="accordion-item"><h3 class="accordion-header"><button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq-pb-1">Frage 1?</button></h3><div id="faq-pb-1" class="accordion-collapse collapse show" data-bs-parent="#bbf-faq-pb"><div class="accordion-body"><p>Antwort 1.</p></div></div></div><div class="accordion-item"><h3 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq-pb-2">Frage 2?</button></h3><div id="faq-pb-2" class="accordion-collapse collapse" data-bs-parent="#bbf-faq-pb"><div class="accordion-body"><p>Antwort 2.</p></div></div></div></div>' });
    bm.add('bbf-html', { label: 'Freies HTML', category: 'Erweitert', content: '<div class="bbf-html-custom"><p>Freier HTML-Inhalt</p></div>' });

    // Dynamic blocks (placeholders)
    var dynamicBlocks = [
        { id: 'program', label: 'Programm', icon: '📋' },
        { id: 'partners', label: 'Partner / Sponsoren', icon: '🤝' },
        { id: 'tickets', label: 'Tickets', icon: '🎫' },
        { id: 'knowledge', label: 'Wissenswertes', icon: '💡' },
        { id: 'area_map', label: 'Geländeplan / Karte', icon: '🗺️' },
        { id: 'teaser_list', label: 'Weitere Events', icon: '📰' },
    ];

    dynamicBlocks.forEach(function(b) {
        bm.add('bbf-' + b.id, {
            label: b.label,
            category: 'Event-Daten',
            content: '<div data-bbf-dynamic="' + b.id + '" style="padding:2rem;background:#f0f4f8;border:2px dashed #94a3b8;border-radius:0.5rem;text-align:center;"><span style="font-size:2rem;display:block;margin-bottom:0.5rem;">' + b.icon + '</span><strong>' + b.label + '</strong><br><small style="color:#64748b;">Wird aus den Eventdaten generiert</small></div>',
        });
    });

    // ── Toolbar Buttons ───────────────────────────────
    document.getElementById('bbf-pb-undo').addEventListener('click', function() { editor.runCommand('core:undo'); });
    document.getElementById('bbf-pb-redo').addEventListener('click', function() { editor.runCommand('core:redo'); });
    document.getElementById('bbf-pb-preview').addEventListener('click', function() { editor.runCommand('core:preview'); });
    document.getElementById('bbf-pb-fullscreen').addEventListener('click', function() { editor.runCommand('core:fullscreen'); });
    document.getElementById('bbf-pb-desktop').addEventListener('click', function() { editor.setDevice('Desktop'); });
    document.getElementById('bbf-pb-tablet').addEventListener('click', function() { editor.setDevice('Tablet'); });
    document.getElementById('bbf-pb-mobile').addEventListener('click', function() { editor.setDevice('Mobile'); });

    // Code editor
    document.getElementById('bbf-pb-code').addEventListener('click', function() {
        var html = editor.getHtml();
        var css = editor.getCss();
        var modal = editor.Modal;
        var el = document.createElement('div');
        el.innerHTML = '<div style="display:flex;gap:0.5rem;margin-bottom:1rem;"><button class="bbf-pb-btn active" data-t="html">HTML</button><button class="bbf-pb-btn" data-t="css">CSS</button></div>' +
            '<div data-p="html"><textarea id="bbf-code-html" style="width:100%;height:400px;font-family:monospace;font-size:13px;border:1px solid #555;border-radius:4px;padding:0.75rem;background:#1a1a2e;color:#e2e8f0;"></textarea></div>' +
            '<div data-p="css" style="display:none;"><textarea id="bbf-code-css" style="width:100%;height:400px;font-family:monospace;font-size:13px;border:1px solid #555;border-radius:4px;padding:0.75rem;background:#1a1a2e;color:#e2e8f0;"></textarea></div>' +
            '<div style="margin-top:1rem;display:flex;gap:0.5rem;"><button class="bbf-pb-btn bbf-pb-btn--primary" id="bbf-code-ok">Übernehmen</button><button class="bbf-pb-btn" id="bbf-code-cancel">Abbrechen</button></div>';
        modal.setTitle('HTML & CSS Editor');
        modal.setContent(el);
        modal.open();
        el.querySelector('#bbf-code-html').value = html;
        el.querySelector('#bbf-code-css').value = css;
        el.querySelectorAll('[data-t]').forEach(function(btn) {
            btn.addEventListener('click', function() {
                el.querySelectorAll('[data-t]').forEach(function(b) { b.classList.remove('active'); });
                btn.classList.add('active');
                el.querySelectorAll('[data-p]').forEach(function(p) { p.style.display = p.dataset.p === btn.dataset.t ? '' : 'none'; });
            });
        });
        el.querySelector('#bbf-code-ok').addEventListener('click', function() {
            editor.setComponents(el.querySelector('#bbf-code-html').value);
            editor.setStyle(el.querySelector('#bbf-code-css').value);
            modal.close();
        });
        el.querySelector('#bbf-code-cancel').addEventListener('click', function() { modal.close(); });
    });

    // ── Save / Load ───────────────────────────────────
    var currentLang = CURRENT_LANG;

    function loadPage() {
        fetch(AJAX_URL + '&is_ajax=1&action=page_load&event_id=' + EVENT_ID + '&lang=' + currentLang)
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.gjs_data) {
                    try { editor.loadProjectData(JSON.parse(data.gjs_data)); }
                    catch(e) { console.warn('Could not load GJS data', e); }
                }
            })
            .catch(function(e) { console.warn('Load failed', e); });
    }

    function savePage() {
        var gjsData = JSON.stringify(editor.getProjectData());
        var htmlRendered = editor.getHtml();
        var cssRendered = editor.getCss();

        fetch(AJAX_URL + '&is_ajax=1&action=page_save', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                event_id: EVENT_ID,
                language_iso: currentLang,
                gjs_data: gjsData,
                html_rendered: htmlRendered,
                css_rendered: cssRendered,
            }),
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) { showToast('Gespeichert', 'success'); }
            else { showToast('Fehler: ' + (data.error || 'Unbekannt'), 'error'); }
        })
        .catch(function() { showToast('Speichern fehlgeschlagen', 'error'); });
    }

    document.getElementById('bbf-pb-save').addEventListener('click', savePage);

    // Ctrl+S / Cmd+S
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 's') { e.preventDefault(); savePage(); }
    });

    // Language switch
    document.getElementById('bbf-pb-lang').addEventListener('change', function() {
        savePage(); // save current first
        currentLang = this.value;
        loadPage();
    });

    // Auto-save every 90s
    setInterval(savePage, 90000);

    // Initial load
    loadPage();

    function showToast(msg, type) {
        var el = document.createElement('div');
        el.className = 'bbf-pb-toast bbf-pb-toast--' + type;
        el.textContent = msg;
        document.body.appendChild(el);
        setTimeout(function() { el.style.opacity = '0'; setTimeout(function() { el.remove(); }, 300); }, 3000);
    }

})();
</script>

</body>
</html>
