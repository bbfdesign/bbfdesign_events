/**
 * BBF Events – Ticket Editor
 * Manages ticket options within the event edit form.
 */
(function () {
    'use strict';

    const container = document.getElementById('bbf-tickets-container');
    if (!container) return;

    const addBtn = document.getElementById('bbf-add-ticket');
    let ticketIndex = container.querySelectorAll('.bbf-ticket-entry').length;

    if (addBtn) {
        addBtn.addEventListener('click', function () {
            const idx = ticketIndex++;
            const categories = window.BBF_TICKET_CATEGORIES || [];

            let catOptions = '<option value="">Keine</option>';
            categories.forEach(function (c) {
                catOptions += `<option value="${c.id}">${c.name}</option>`;
            });

            const html = `
                <div class="bbf-ticket-entry border rounded p-3 mb-3" data-index="${idx}">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h6 class="mb-0">Ticket ${idx + 1}</h6>
                        <button type="button" class="btn btn-sm btn-outline-danger bbf-remove-ticket"><i class="fa fa-times"></i></button>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Name (DE) *</label>
                            <input type="text" name="tickets[${idx}][name_ger]" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Typ</label>
                            <select name="tickets[${idx}][source_type]" class="form-select bbf-ticket-type-select">
                                <option value="external">Externer Link</option>
                                <option value="wawi_article">Wawi-Artikel</option>
                                <option value="plugin_native">Plugin-Ticket</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Kategorie</label>
                            <select name="tickets[${idx}][category_id]" class="form-select">${catOptions}</select>
                        </div>

                        <div class="col-md-6 bbf-ticket-field-external">
                            <label class="form-label">Externe URL</label>
                            <input type="url" name="tickets[${idx}][external_url]" class="form-control" placeholder="https://...">
                        </div>
                        <div class="col-md-6 bbf-ticket-field-external">
                            <label class="form-label">Anbieter</label>
                            <input type="text" name="tickets[${idx}][external_provider]" class="form-control" placeholder="z.B. Eventbrite">
                        </div>

                        <div class="col-md-6 bbf-ticket-field-wawi d-none">
                            <label class="form-label">Wawi Artikel-ID (kArtikel)</label>
                            <input type="number" name="tickets[${idx}][wawi_article_id]" class="form-control">
                        </div>
                        <div class="col-md-6 bbf-ticket-field-wawi d-none">
                            <label class="form-label">Artikelnummer</label>
                            <input type="text" name="tickets[${idx}][wawi_article_no]" class="form-control">
                        </div>

                        <div class="col-md-3 bbf-ticket-field-native d-none">
                            <label class="form-label">Preis brutto</label>
                            <input type="number" step="0.01" name="tickets[${idx}][price_gross]" class="form-control">
                        </div>
                        <div class="col-md-3 bbf-ticket-field-native d-none">
                            <label class="form-label">MwSt. %</label>
                            <input type="number" step="0.01" name="tickets[${idx}][tax_rate]" class="form-control" value="19">
                        </div>
                        <div class="col-md-3 bbf-ticket-field-native d-none">
                            <label class="form-label">Kontingent</label>
                            <input type="number" name="tickets[${idx}][max_quantity]" class="form-control">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Beschreibung (DE)</label>
                            <textarea name="tickets[${idx}][description_ger]" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">CTA-Label</label>
                            <input type="text" name="tickets[${idx}][cta_label_ger]" class="form-control" placeholder="z.B. Jetzt kaufen">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Hinweis</label>
                            <input type="text" name="tickets[${idx}][hint_ger]" class="form-control" placeholder="z.B. Begrenzt auf 200">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Verfügbar ab</label>
                            <input type="datetime-local" name="tickets[${idx}][available_from]" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Verfügbar bis</label>
                            <input type="datetime-local" name="tickets[${idx}][available_to]" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" name="tickets[${idx}][is_active]" checked>
                                <label class="form-check-label">Aktiv</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Sortierung</label>
                            <input type="number" name="tickets[${idx}][sort_order]" value="0" class="form-control" min="0">
                        </div>
                    </div>
                </div>`;
            container.insertAdjacentHTML('beforeend', html);
        });
    }

    // ── Remove ticket ─────────────────────────────────────
    container.addEventListener('click', function (e) {
        if (e.target.closest('.bbf-remove-ticket')) {
            e.target.closest('.bbf-ticket-entry').remove();
        }
    });

    // ── Source type toggle ─────────────────────────────────
    container.addEventListener('change', function (e) {
        if (e.target.classList.contains('bbf-ticket-type-select')) {
            const entry = e.target.closest('.bbf-ticket-entry');
            const type = e.target.value;

            entry.querySelectorAll('.bbf-ticket-field-external').forEach(function (el) { el.classList.toggle('d-none', type !== 'external'); });
            entry.querySelectorAll('.bbf-ticket-field-wawi').forEach(function (el) { el.classList.toggle('d-none', type !== 'wawi_article'); });
            entry.querySelectorAll('.bbf-ticket-field-native').forEach(function (el) { el.classList.toggle('d-none', type !== 'plugin_native'); });
        }
    });

})();
