<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <strong>Programm / Sessions</strong>
        <button type="button" class="btn btn-sm btn-outline-primary" id="bbf-add-program-entry">
            <i class="fa fa-plus"></i> Programmpunkt hinzufügen
        </button>
    </div>
    <div class="card-body">
        <div id="bbf-program-container">
            {if !empty($programEntries)}
                {foreach $programEntries as $idx => $entry}
                    <div class="bbf-program-entry border rounded p-3 mb-3" data-index="{$idx}">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="mb-0">Programmpunkt {$idx + 1}</h6>
                            <button type="button" class="btn btn-sm btn-outline-danger bbf-remove-program"><i class="fa fa-times"></i></button>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Titel (DE) *</label>
                                <input type="text" name="program[{$idx}][title_ger]" value="{$entry->getTitle()|escape:'html'}" class="form-control" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Startzeit</label>
                                <input type="time" name="program[{$idx}][time_start]" value="{if $entry->timeStart}{$entry->timeStart->format('H:i')}{/if}" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Endzeit</label>
                                <input type="time" name="program[{$idx}][time_end]" value="{if $entry->timeEnd}{$entry->timeEnd->format('H:i')}{/if}" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Sprecher</label>
                                <input type="text" name="program[{$idx}][speaker_name]" value="{$entry->speakerName|default:''|escape:'html'}" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Beschreibung (DE)</label>
                                <textarea name="program[{$idx}][description_ger]" class="form-control" rows="1">{$entry->getDescription()|escape:'html'}</textarea>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" name="program[{$idx}][is_highlight]" {if $entry->isHighlight}checked{/if}>
                                    <label class="form-check-label">Highlight</label>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="program[{$idx}][id]" value="{$entry->id}">
                    </div>
                {/foreach}
            {else}
                <p class="text-muted text-center py-3">Noch keine Programmpunkte.</p>
            {/if}
        </div>
    </div>
</div>
<script src="../../adminmenu/js/program-editor.js"></script>
