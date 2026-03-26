<div class="card">
    <div class="card-header"><strong>Area / Karten zuordnen</strong></div>
    <div class="card-body">
        {if !empty($allAreaMaps)}
            {foreach $allAreaMaps as $map}
                <div class="form-check">
                    <input class="form-check-input" type="checkbox"
                           name="area_maps[]" value="{$map->id}"
                           id="area_{$map->id}"
                           {if in_array($map->id, $assignedAreaIds|default:[])}checked{/if}>
                    <label class="form-check-label" for="area_{$map->id}">
                        {$map->title|default:$map->slug}
                        <small class="text-muted ms-1">({$map->map_type})</small>
                        {if !$map->is_active} <span class="badge bg-secondary">inaktiv</span>{/if}
                    </label>
                </div>
            {/foreach}
        {else}
            <p class="text-muted">Keine Karten vorhanden. <a href="{$postURL}&bbf_page=areas&action=create">Karte erstellen</a></p>
        {/if}
    </div>
</div>
