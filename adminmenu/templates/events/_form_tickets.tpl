<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <strong>Ticket-Optionen</strong>
        <button type="button" class="btn btn-sm btn-outline-primary" id="bbf-add-ticket">
            <i class="fa fa-plus"></i> Ticket hinzufügen
        </button>
    </div>
    <div class="card-body">
        <div id="bbf-tickets-container">
            {if !empty($eventTickets)}
                {foreach $eventTickets as $idx => $ticket}
                    <div class="bbf-ticket-entry border rounded p-3 mb-3" data-index="{$idx}">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="mb-0">Ticket: {$ticket->getName()|default:'(kein Name)'}</h6>
                            <button type="button" class="btn btn-sm btn-outline-danger bbf-remove-ticket"><i class="fa fa-times"></i></button>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Name (DE)</label>
                                <input type="text" name="tickets[{$idx}][name_ger]" value="{$ticket->getName()|escape:'html'}" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Typ</label>
                                <select name="tickets[{$idx}][source_type]" class="form-select bbf-ticket-type-select">
                                    <option value="external"{if $ticket->sourceType->value === 'external'} selected{/if}>Externer Link</option>
                                    <option value="wawi_article"{if $ticket->sourceType->value === 'wawi_article'} selected{/if}>Wawi-Artikel</option>
                                    <option value="plugin_native"{if $ticket->sourceType->value === 'plugin_native'} selected{/if}>Plugin-Ticket</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Sortierung</label>
                                <input type="number" name="tickets[{$idx}][sort_order]" value="{$ticket->sortOrder}" class="form-control" min="0">
                            </div>
                        </div>
                        <input type="hidden" name="tickets[{$idx}][id]" value="{$ticket->id}">
                    </div>
                {/foreach}
            {else}
                <p class="text-muted text-center py-3">Noch keine Tickets.</p>
            {/if}
        </div>
    </div>
</div>
<script src="../../adminmenu/js/ticket-editor.js"></script>
