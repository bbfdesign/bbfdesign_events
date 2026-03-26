/**
 * BBF Events – GrapesJS Pagebuilder
 * Main entry point for Vite bundling.
 * Pattern adapted from BBF Formbuilder.
 */

import grapesjs from 'grapesjs';
import grapesjsPresetWebpage from 'grapesjs-preset-webpage';
import bbfEventBlocks from './plugins/bbf-event-blocks.js';

window.BbfPagebuilder = {
    editor: null,

    init(config) {
        const {
            container = '#bbf-gjs-editor',
            eventId,
            languageIso = 'ger',
            csrfToken = '',
            postURL = '',
            canvasStyles = [],
        } = config;

        const containerEl = document.querySelector(container);
        if (!containerEl) {
            console.error('BBF Pagebuilder: Container not found:', container);
            return null;
        }

        if (containerEl.offsetHeight < 100) {
            containerEl.style.minHeight = '500px';
        }

        if (this.editor) {
            try { this.editor.destroy(); } catch(e) {}
            this.editor = null;
        }

        console.log('BBF Pagebuilder: Initializing GrapesJS in', container);

        try {
            const editor = grapesjs.init({
                container,
                fromElement: false,
                height: '100%',
                width: 'auto',
                storageManager: false,

                canvas: {
                    styles: [...canvasStyles],
                },

                panels: { defaults: [] },

                deviceManager: {
                    devices: [
                        { name: 'Desktop', width: '' },
                        { name: 'Tablet', width: '768px', widthMedia: '992px' },
                        { name: 'Mobile', width: '375px', widthMedia: '768px' },
                    ],
                },

                blockManager: {
                    appendTo: '#bbf-gjs-blocks',
                },

                styleManager: {
                    appendTo: '#bbf-gjs-styles',
                    sectors: [
                        { name: 'Abmessungen', properties: ['width','min-width','max-width','height','min-height','padding','margin'] },
                        { name: 'Typografie', properties: ['font-family','font-size','font-weight','letter-spacing','line-height','color','text-align','text-transform'] },
                        { name: 'Hintergrund', properties: ['background-color','background-image','background-size','background-position'] },
                        { name: 'Rahmen & Ecken', properties: ['border','border-radius','box-shadow'] },
                    ],
                },

                traitManager: {
                    appendTo: '#bbf-gjs-traits',
                },

                plugins: [grapesjsPresetWebpage, bbfEventBlocks],
                pluginsOpts: {
                    [grapesjsPresetWebpage]: {},
                    [bbfEventBlocks]: {},
                },
            });

            // ── Toolbar Bindings ─────────────────────────────
            setupHtmlToolbar(editor);

            // ── Keyboard Shortcut ────────────────────────────
            document.addEventListener('keydown', (e) => {
                if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                    e.preventDefault();
                    savePage();
                }
            });

            // ── Save Button ──────────────────────────────────
            const saveBtn = document.getElementById('bbf-btn-save');
            if (saveBtn) {
                saveBtn.addEventListener('click', () => savePage());
            }

            // ── Language Switch ──────────────────────────────
            let currentLang = languageIso;
            const langSelect = document.getElementById('bbf-pb-lang');
            if (langSelect) {
                langSelect.addEventListener('change', () => {
                    savePage().then(() => {
                        currentLang = langSelect.value;
                        loadPage();
                    });
                });
            }

            // ── Save ─────────────────────────────────────────
            async function savePage() {
                const gjsData = JSON.stringify(editor.getProjectData());
                const html = editor.getHtml();
                const css = editor.getCss();

                try {
                    const formData = new FormData();
                    formData.append('action', 'page_save');
                    formData.append('event_id', eventId || '');
                    formData.append('language_iso', currentLang);
                    formData.append('gjs_data', gjsData);
                    formData.append('html_rendered', html);
                    formData.append('css_rendered', css);
                    formData.append('is_ajax', '1');
                    formData.append('jtl_token', csrfToken);

                    const response = await fetch(postURL, { method: 'POST', body: formData });
                    const data = await response.json();

                    if (data.success) {
                        showNotification('Seitenlayout gespeichert', 'success');
                    } else {
                        showNotification('Fehler: ' + (data.error || 'Unbekannt'), 'error');
                    }
                } catch (err) {
                    showNotification('Speichern fehlgeschlagen', 'error');
                    console.error('BBF Pagebuilder: Save failed', err);
                }
            }

            // ── Load ─────────────────────────────────────────
            async function loadPage() {
                try {
                    const formData = new FormData();
                    formData.append('action', 'page_load');
                    formData.append('event_id', eventId);
                    formData.append('lang', currentLang);
                    formData.append('is_ajax', '1');
                    formData.append('jtl_token', csrfToken);

                    const response = await fetch(postURL, { method: 'POST', body: formData });
                    const data = await response.json();

                    if (data.gjs_data) {
                        editor.loadProjectData(JSON.parse(data.gjs_data));
                    }
                } catch (err) {
                    console.warn('BBF Pagebuilder: Could not load page data', err);
                }
            }

            // Initial load
            if (eventId) {
                loadPage();
            }

            this.editor = editor;
            console.log('BBF Pagebuilder: GrapesJS initialized successfully');
            return editor;

        } catch (err) {
            console.error('BBF Pagebuilder: GrapesJS init failed:', err);
            containerEl.innerHTML =
                '<div style="padding:40px;text-align:center;color:#dc3545;">' +
                '<p><strong>Editor-Fehler</strong></p>' +
                '<p style="font-size:13px;">' + err.message + '</p></div>';
            return null;
        }
    },

    destroy() {
        if (this.editor) {
            try { this.editor.destroy(); } catch(e) {}
            this.editor = null;
        }
    },
};

// ── HTML Toolbar → GrapesJS Commands ─────────────────────────
function setupHtmlToolbar(editor) {
    editor.Commands.add('set-device-desktop', { run: (ed) => ed.setDevice('Desktop') });
    editor.Commands.add('set-device-tablet', { run: (ed) => ed.setDevice('Tablet') });
    editor.Commands.add('set-device-mobile', { run: (ed) => ed.setDevice('Mobile') });

    editor.Commands.add('bbf:open-code', {
        run(editor) {
            openCodeEditorModal(editor, editor.getHtml(), editor.getCss());
        },
    });

    const bindings = {
        'bbf-btn-undo':    () => editor.runCommand('core:undo'),
        'bbf-btn-redo':    () => editor.runCommand('core:redo'),
        'bbf-btn-preview': () => editor.runCommand('core:preview'),
        'bbf-btn-code':    () => editor.runCommand('bbf:open-code'),
        'bbf-btn-desktop': () => editor.runCommand('set-device-desktop'),
        'bbf-btn-tablet':  () => editor.runCommand('set-device-tablet'),
        'bbf-btn-mobile':  () => editor.runCommand('set-device-mobile'),
    };

    Object.entries(bindings).forEach(([id, handler]) => {
        const el = document.getElementById(id);
        if (el) el.addEventListener('click', handler);
    });
}

// ── Code Editor Modal ─────────────────────────────────────────
function openCodeEditorModal(editor, html, css) {
    const modal = editor.Modal;
    modal.setTitle('HTML & CSS Editor');

    const content = document.createElement('div');
    content.innerHTML = `
        <div style="display:flex;gap:0.5rem;margin-bottom:1rem;">
            <button class="btn btn-sm btn-primary active" data-tab="html">HTML</button>
            <button class="btn btn-sm btn-outline-secondary" data-tab="css">CSS</button>
        </div>
        <div data-panel="html">
            <textarea id="bbf-code-html" style="width:100%;height:400px;font-family:monospace;font-size:13px;border:1px solid #dee2e6;border-radius:4px;padding:0.75rem;">${escapeHtml(html)}</textarea>
        </div>
        <div data-panel="css" style="display:none;">
            <textarea id="bbf-code-css" style="width:100%;height:400px;font-family:monospace;font-size:13px;border:1px solid #dee2e6;border-radius:4px;padding:0.75rem;">${escapeHtml(css)}</textarea>
        </div>
        <div style="margin-top:1rem;display:flex;gap:0.5rem;">
            <button class="btn btn-primary" id="bbf-code-apply">Übernehmen</button>
            <button class="btn btn-secondary" id="bbf-code-cancel">Abbrechen</button>
        </div>
    `;

    modal.setContent(content);
    modal.open();

    content.querySelectorAll('[data-tab]').forEach((btn) => {
        btn.addEventListener('click', () => {
            content.querySelectorAll('[data-tab]').forEach((b) => { b.classList.remove('btn-primary','active'); b.classList.add('btn-outline-secondary'); });
            btn.classList.add('btn-primary','active'); btn.classList.remove('btn-outline-secondary');
            content.querySelectorAll('[data-panel]').forEach((p) => { p.style.display = p.dataset.panel === btn.dataset.tab ? '' : 'none'; });
        });
    });

    content.querySelector('#bbf-code-apply').addEventListener('click', () => {
        editor.setComponents(content.querySelector('#bbf-code-html').value);
        editor.setStyle(content.querySelector('#bbf-code-css').value);
        modal.close();
    });

    content.querySelector('#bbf-code-cancel').addEventListener('click', () => modal.close());
}

function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

function showNotification(message, type = 'info') {
    const el = document.createElement('div');
    el.style.cssText = `
        position:fixed;bottom:1rem;right:1rem;z-index:10000;
        padding:0.75rem 1.25rem;border-radius:0.5rem;
        font-size:0.875rem;font-weight:500;
        background:${type === 'success' ? '#059669' : type === 'error' ? '#DC2626' : '#2563EB'};
        color:#fff;box-shadow:0 4px 12px rgba(0,0,0,0.2);
        transition:opacity 0.3s;opacity:1;
    `;
    el.textContent = message;
    document.body.appendChild(el);
    setTimeout(() => { el.style.opacity = '0'; setTimeout(() => el.remove(), 300); }, 3000);
}
