<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <strong>Verlinkungen</strong>
        <button type="button" class="btn btn-sm btn-outline-primary" id="bbf-add-link">
            <i class="fa fa-plus"></i> Link hinzufügen
        </button>
    </div>
    <div class="card-body">
        <div id="bbf-links-container">
            {if !empty($eventLinks)}
                {foreach $eventLinks as $idx => $link}
                    <div class="bbf-link-entry border rounded p-3 mb-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge bg-light text-dark">{$link->link_type}</span>
                            <button type="button" class="btn btn-sm btn-outline-danger bbf-remove-link"><i class="fa fa-times"></i></button>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Typ</label>
                                <select name="links[{$idx}][link_type]" class="form-select form-select-sm">
                                    <option value="external"{if $link->link_type === 'external'} selected{/if}>Extern</option>
                                    <option value="internal"{if $link->link_type === 'internal'} selected{/if}>Intern</option>
                                    <option value="product"{if $link->link_type === 'product'} selected{/if}>Produkt</option>
                                    <option value="category"{if $link->link_type === 'category'} selected{/if}>Kategorie</option>
                                    <option value="cms"{if $link->link_type === 'cms'} selected{/if}>CMS</option>
                                    <option value="event"{if $link->link_type === 'event'} selected{/if}>Event</option>
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label">URL / Ziel</label>
                                <input type="text" name="links[{$idx}][target_url]" value="{$link->target_url|default:''|escape:'html'}" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Label (DE)</label>
                                <input type="text" name="links[{$idx}][label_ger]" value="{$link->label|default:''|escape:'html'}" class="form-control form-control-sm">
                            </div>
                        </div>
                        <input type="hidden" name="links[{$idx}][id]" value="{$link->id}">
                    </div>
                {/foreach}
            {else}
                <p class="text-muted text-center py-3" id="bbf-no-links">Noch keine Links.</p>
            {/if}
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var container = document.getElementById('bbf-links-container');
    var addBtn = document.getElementById('bbf-add-link');
    var idx = container.querySelectorAll('.bbf-link-entry').length;

    addBtn.addEventListener('click', function() {
        var noMsg = document.getElementById('bbf-no-links');
        if (noMsg) noMsg.style.display = 'none';
        var i = idx++;
        container.insertAdjacentHTML('beforeend',
            '<div class="bbf-link-entry border rounded p-3 mb-3">' +
            '<div class="d-flex justify-content-between align-items-start mb-2"><span class="badge bg-light text-dark">Neuer Link</span><button type="button" class="btn btn-sm btn-outline-danger bbf-remove-link"><i class="fa fa-times"></i></button></div>' +
            '<div class="row g-3">' +
            '<div class="col-md-3"><label class="form-label">Typ</label><select name="links['+i+'][link_type]" class="form-select form-select-sm"><option value="external">Extern</option><option value="internal">Intern</option><option value="product">Produkt</option><option value="event">Event</option></select></div>' +
            '<div class="col-md-5"><label class="form-label">URL / Ziel</label><input type="text" name="links['+i+'][target_url]" class="form-control form-control-sm" placeholder="https://..."></div>' +
            '<div class="col-md-4"><label class="form-label">Label (DE)</label><input type="text" name="links['+i+'][label_ger]" class="form-control form-control-sm"></div>' +
            '</div></div>');
    });

    container.addEventListener('click', function(e) {
        if (e.target.closest('.bbf-remove-link')) e.target.closest('.bbf-link-entry').remove();
    });
});
</script>
