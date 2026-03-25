<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <strong>Termine</strong>
        <button type="button" class="btn btn-sm btn-outline-primary" id="bbf-add-date">
            <i class="fa fa-plus"></i> Termin hinzufügen
        </button>
    </div>
    <div class="card-body">
        <div id="bbf-dates-container">
            {if !empty($dates)}
                {foreach $dates as $idx => $date}
                    <div class="bbf-date-entry border rounded p-3 mb-3" data-index="{$idx}">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="mb-0">Termin {$idx + 1}</h6>
                            <button type="button" class="btn btn-sm btn-outline-danger bbf-remove-date" title="Entfernen">
                                <i class="fa fa-times"></i>
                            </button>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Startdatum *</label>
                                <input type="date" name="date_start[{$idx}]"
                                       value="{$date->dateStart->format('Y-m-d')}"
                                       class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Enddatum</label>
                                <input type="date" name="date_end[{$idx}]"
                                       value="{if $date->dateEnd}{$date->dateEnd->format('Y-m-d')}{/if}"
                                       class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">&nbsp;</label>
                                <div class="form-check mt-2">
                                    <input class="form-check-input bbf-allday-toggle" type="checkbox"
                                           name="date_allday[{$idx}]" id="allday_{$idx}"
                                           {if $date->isAllday}checked{/if}>
                                    <label class="form-check-label" for="allday_{$idx}">Ganztägig</label>
                                </div>
                            </div>
                        </div>

                        {* Zeitfenster *}
                        <div class="bbf-timeslots mt-3{if $date->isAllday} d-none{/if}">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <small class="fw-bold text-muted">Zeitfenster</small>
                                <button type="button" class="btn btn-sm btn-link bbf-add-timeslot">+ Zeitfenster</button>
                            </div>
                            <div class="bbf-timeslots-list">
                                {foreach $date->timeSlots as $si => $slot}
                                    <div class="row g-2 mb-2 bbf-timeslot-entry">
                                        <div class="col-3">
                                            <input type="time" name="timeslot_start[{$idx}][{$si}]"
                                                   value="{$slot->timeStart->format('H:i')}"
                                                   class="form-control form-control-sm">
                                        </div>
                                        <div class="col-3">
                                            <input type="time" name="timeslot_end[{$idx}][{$si}]"
                                                   value="{if $slot->timeEnd}{$slot->timeEnd->format('H:i')}{/if}"
                                                   class="form-control form-control-sm" placeholder="Ende">
                                        </div>
                                        <div class="col-4">
                                            <input type="text" name="timeslot_label[{$idx}][{$si}]"
                                                   value="{$slot->label|default:''|escape:'html'}"
                                                   class="form-control form-control-sm" placeholder="Label (z.B. Einlass)">
                                        </div>
                                        <div class="col-2">
                                            <button type="button" class="btn btn-sm btn-outline-danger bbf-remove-timeslot">
                                                <i class="fa fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                {/foreach}
                            </div>
                        </div>
                    </div>
                {/foreach}
            {/if}
        </div>

        {if empty($dates)}
            <p class="text-muted text-center py-3" id="bbf-no-dates">
                Noch keine Termine. Klicken Sie auf "Termin hinzufügen".
            </p>
        {/if}
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('bbf-dates-container');
    const addBtn = document.getElementById('bbf-add-date');
    const noMsg = document.getElementById('bbf-no-dates');
    let dateIndex = container.querySelectorAll('.bbf-date-entry').length;

    addBtn.addEventListener('click', function() {
        if (noMsg) noMsg.style.display = 'none';
        const idx = dateIndex++;
        const html = `
            <div class="bbf-date-entry border rounded p-3 mb-3" data-index="${idx}">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h6 class="mb-0">Termin ${idx + 1}</h6>
                    <button type="button" class="btn btn-sm btn-outline-danger bbf-remove-date"><i class="fa fa-times"></i></button>
                </div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Startdatum *</label>
                        <input type="date" name="date_start[${idx}]" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Enddatum</label>
                        <input type="date" name="date_end[${idx}]" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">&nbsp;</label>
                        <div class="form-check mt-2">
                            <input class="form-check-input bbf-allday-toggle" type="checkbox" name="date_allday[${idx}]" id="allday_${idx}" checked>
                            <label class="form-check-label" for="allday_${idx}">Ganztägig</label>
                        </div>
                    </div>
                </div>
                <div class="bbf-timeslots mt-3 d-none">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <small class="fw-bold text-muted">Zeitfenster</small>
                        <button type="button" class="btn btn-sm btn-link bbf-add-timeslot">+ Zeitfenster</button>
                    </div>
                    <div class="bbf-timeslots-list"></div>
                </div>
            </div>`;
        container.insertAdjacentHTML('beforeend', html);
    });

    container.addEventListener('click', function(e) {
        if (e.target.closest('.bbf-remove-date')) {
            e.target.closest('.bbf-date-entry').remove();
        }
        if (e.target.closest('.bbf-remove-timeslot')) {
            e.target.closest('.bbf-timeslot-entry').remove();
        }
        if (e.target.closest('.bbf-add-timeslot')) {
            const entry = e.target.closest('.bbf-date-entry');
            const idx = entry.dataset.index;
            const list = entry.querySelector('.bbf-timeslots-list');
            const si = list.querySelectorAll('.bbf-timeslot-entry').length;
            list.insertAdjacentHTML('beforeend', `
                <div class="row g-2 mb-2 bbf-timeslot-entry">
                    <div class="col-3"><input type="time" name="timeslot_start[${idx}][${si}]" class="form-control form-control-sm"></div>
                    <div class="col-3"><input type="time" name="timeslot_end[${idx}][${si}]" class="form-control form-control-sm" placeholder="Ende"></div>
                    <div class="col-4"><input type="text" name="timeslot_label[${idx}][${si}]" class="form-control form-control-sm" placeholder="Label"></div>
                    <div class="col-2"><button type="button" class="btn btn-sm btn-outline-danger bbf-remove-timeslot"><i class="fa fa-times"></i></button></div>
                </div>`);
        }
    });

    container.addEventListener('change', function(e) {
        if (e.target.classList.contains('bbf-allday-toggle')) {
            const slots = e.target.closest('.bbf-date-entry').querySelector('.bbf-timeslots');
            slots.classList.toggle('d-none', e.target.checked);
        }
    });
});
</script>
