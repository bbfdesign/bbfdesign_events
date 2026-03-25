/**
 * BBF Events – GrapesJS Storage (Save/Load via AJAX)
 */

export function setupStorage(editor, eventId, languageIso, csrfToken) {
    const apiBase = '/admin/plugin/bbfdesign_events/api/page';

    // ── Load ──────────────────────────────────────────────
    async function loadPage() {
        try {
            const response = await fetch(
                `${apiBase}/load?event_id=${eventId}&lang=${languageIso}`
            );
            const data = await response.json();

            if (data.gjs_data) {
                editor.loadProjectData(JSON.parse(data.gjs_data));
            }
        } catch (err) {
            console.warn('BBF Pagebuilder: Could not load page data', err);
        }
    }

    // ── Save ──────────────────────────────────────────────
    async function savePage() {
        const gjsData = JSON.stringify(editor.getProjectData());
        const htmlRendered = editor.getHtml();
        const cssRendered = editor.getCss();

        try {
            const response = await fetch(`${apiBase}/save`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': csrfToken,
                },
                body: JSON.stringify({
                    event_id: eventId,
                    language_iso: languageIso,
                    gjs_data: gjsData,
                    html_rendered: htmlRendered,
                    css_rendered: cssRendered,
                }),
            });

            if (response.ok) {
                showNotification('Seitenlayout gespeichert', 'success');
            } else {
                showNotification('Fehler beim Speichern', 'error');
            }
        } catch (err) {
            showNotification('Fehler beim Speichern', 'error');
            console.error('BBF Pagebuilder: Save failed', err);
        }
    }

    // Save button
    const saveBtn = document.getElementById('bbf-save-page');
    if (saveBtn) {
        saveBtn.addEventListener('click', savePage);
    }

    // Keyboard shortcut: Ctrl+S / Cmd+S
    document.addEventListener('keydown', (e) => {
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            savePage();
        }
    });

    // Auto-save every 60 seconds
    let autoSaveInterval = setInterval(savePage, 60000);

    // Cleanup on editor destroy
    editor.on('destroy', () => {
        clearInterval(autoSaveInterval);
    });

    // Initial load
    loadPage();
}

function showNotification(message, type = 'info') {
    const container = document.getElementById('bbf-notifications') || document.body;
    const el = document.createElement('div');
    el.className = `bbf-notification bbf-notification--${type}`;
    el.style.cssText = `
        position: fixed; bottom: 1rem; right: 1rem; z-index: 10000;
        padding: 0.75rem 1.25rem; border-radius: 0.5rem;
        font-size: 0.875rem; font-weight: 500;
        background: ${type === 'success' ? '#059669' : type === 'error' ? '#DC2626' : '#2563EB'};
        color: #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        transition: opacity 0.3s; opacity: 1;
    `;
    el.textContent = message;
    container.appendChild(el);

    setTimeout(() => {
        el.style.opacity = '0';
        setTimeout(() => el.remove(), 300);
    }, 3000);
}
