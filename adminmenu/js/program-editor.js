/**
 * BBF Events – Program Editor
 * Manages program entries (sessions) within the event edit form.
 * Loaded on the event edit page when the program tab is active.
 */
(function () {
    'use strict';

    const container = document.getElementById('bbf-program-container');
    if (!container) return;

    const addBtn = document.getElementById('bbf-add-program-entry');
    let entryIndex = container.querySelectorAll('.bbf-program-entry').length;

    // ── Add entry ─────────────────────────────────────────
    if (addBtn) {
        addBtn.addEventListener('click', function () {
            const idx = entryIndex++;
            const categories = window.BBF_PROGRAM_CATEGORIES || [];

            let catOptions = '<option value="">Keine</option>';
            categories.forEach(function (c) {
                catOptions += `<option value="${c.id}">${c.name}</option>`;
            });

            const html = `
                <div class="bbf-program-entry border rounded p-3 mb-3" data-index="${idx}">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h6 class="mb-0">Programmpunkt ${idx + 1}</h6>
                        <button type="button" class="btn btn-sm btn-outline-danger bbf-remove-program"><i class="fa fa-times"></i></button>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Titel (DE) *</label>
                            <input type="text" name="program[${idx}][title_ger]" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Startzeit</label>
                            <input type="time" name="program[${idx}][time_start]" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Endzeit</label>
                            <input type="time" name="program[${idx}][time_end]" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Sprecher</label>
                            <input type="text" name="program[${idx}][speaker_name]" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Kategorie</label>
                            <select name="program[${idx}][category_id]" class="form-select">${catOptions}</select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Beschreibung (DE)</label>
                            <textarea name="program[${idx}][description_ger]" class="form-control" rows="1"></textarea>
                        </div>
                        <div class="col-md-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="program[${idx}][is_highlight]" id="prog_hl_${idx}">
                                <label class="form-check-label" for="prog_hl_${idx}">Highlight</label>
                            </div>
                        </div>
                    </div>
                </div>`;
            container.insertAdjacentHTML('beforeend', html);
        });
    }

    // ── Remove entry ──────────────────────────────────────
    container.addEventListener('click', function (e) {
        if (e.target.closest('.bbf-remove-program')) {
            e.target.closest('.bbf-program-entry').remove();
        }
    });

    // ── Sortable (if Sortable.js is available) ────────────
    if (typeof Sortable !== 'undefined') {
        new Sortable(container, {
            handle: '.bbf-program-entry',
            animation: 150,
            ghostClass: 'bg-light',
        });
    }

})();
