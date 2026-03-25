/**
 * BBF Events – GrapesJS Custom Panels & Toolbar
 */

export function setupBbfPanels(editor) {
    // ── Toolbar ───────────────────────────────────────────
    editor.Panels.addPanel({
        id: 'bbf-toolbar',
        el: '#bbf-toolbar',
        buttons: [
            { id: 'undo', className: 'fa fa-undo', command: 'core:undo', attributes: { title: 'Rückgängig' } },
            { id: 'redo', className: 'fa fa-redo', command: 'core:redo', attributes: { title: 'Wiederholen' } },
            { id: 'preview', className: 'fa fa-eye', command: 'core:preview', attributes: { title: 'Vorschau' } },
            { id: 'code', className: 'fa fa-code', command: 'bbf:open-code', attributes: { title: 'HTML-Editor' } },
            { id: 'fullscreen', className: 'fa fa-expand', command: 'core:fullscreen', attributes: { title: 'Vollbild' } },
            { id: 'clear', className: 'fa fa-trash', command: 'core:canvas-clear', attributes: { title: 'Canvas leeren' } },
        ],
    });

    // ── Responsive Toggle ─────────────────────────────────
    editor.Panels.addPanel({
        id: 'bbf-devices',
        el: '#bbf-devices',
        buttons: [
            { id: 'desktop', command: 'set-device-desktop', active: true, className: 'fa fa-desktop', attributes: { title: 'Desktop' } },
            { id: 'tablet', command: 'set-device-tablet', className: 'fa fa-tablet-alt', attributes: { title: 'Tablet' } },
            { id: 'mobile', command: 'set-device-mobile', className: 'fa fa-mobile-alt', attributes: { title: 'Mobile' } },
        ],
    });

    editor.Commands.add('set-device-desktop', { run: (ed) => ed.setDevice('Desktop') });
    editor.Commands.add('set-device-tablet', { run: (ed) => ed.setDevice('Tablet') });
    editor.Commands.add('set-device-mobile', { run: (ed) => ed.setDevice('Mobile') });

    // ── HTML/CSS Code Editor ──────────────────────────────
    editor.Commands.add('bbf:open-code', {
        run(editor) {
            const html = editor.getHtml();
            const css = editor.getCss();
            openCodeEditorModal(editor, html, css);
        },
    });
}

function openCodeEditorModal(editor, html, css) {
    const modal = editor.Modal;
    modal.setTitle('HTML & CSS Editor');

    const content = document.createElement('div');
    content.className = 'bbf-code-editor';
    content.innerHTML = `
        <div class="bbf-code-editor__tabs" style="display:flex;gap:0.5rem;margin-bottom:1rem;">
            <button class="btn btn-sm btn-primary active" data-tab="html">HTML</button>
            <button class="btn btn-sm btn-outline-secondary" data-tab="css">CSS</button>
        </div>
        <div class="bbf-code-editor__panel" data-panel="html">
            <textarea id="bbf-code-html" style="width:100%;height:400px;font-family:monospace;font-size:13px;border:1px solid #dee2e6;border-radius:4px;padding:0.75rem;">${escapeHtml(html)}</textarea>
        </div>
        <div class="bbf-code-editor__panel" data-panel="css" style="display:none;">
            <textarea id="bbf-code-css" style="width:100%;height:400px;font-family:monospace;font-size:13px;border:1px solid #dee2e6;border-radius:4px;padding:0.75rem;">${escapeHtml(css)}</textarea>
        </div>
        <div style="margin-top:1rem;display:flex;gap:0.5rem;">
            <button class="btn btn-primary" id="bbf-code-apply">Übernehmen</button>
            <button class="btn btn-secondary" id="bbf-code-cancel">Abbrechen</button>
        </div>
    `;

    modal.setContent(content);
    modal.open();

    // Tab switching
    content.querySelectorAll('[data-tab]').forEach((btn) => {
        btn.addEventListener('click', () => {
            content.querySelectorAll('[data-tab]').forEach((b) => {
                b.classList.remove('btn-primary', 'active');
                b.classList.add('btn-outline-secondary');
            });
            btn.classList.add('btn-primary', 'active');
            btn.classList.remove('btn-outline-secondary');

            content.querySelectorAll('[data-panel]').forEach((p) => {
                p.style.display = p.dataset.panel === btn.dataset.tab ? '' : 'none';
            });
        });
    });

    content.querySelector('#bbf-code-apply').addEventListener('click', () => {
        const newHtml = content.querySelector('#bbf-code-html').value;
        const newCss = content.querySelector('#bbf-code-css').value;
        editor.setComponents(newHtml);
        editor.setStyle(newCss);
        modal.close();
    });

    content.querySelector('#bbf-code-cancel').addEventListener('click', () => {
        modal.close();
    });
}

function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}
