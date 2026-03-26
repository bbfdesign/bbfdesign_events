<div class="card">
    <div class="card-header"><strong>Wissenswertes zuordnen</strong></div>
    <div class="card-body">
        {if !empty($allKnowledgeItems)}
            {foreach $allKnowledgeItems as $item}
                <div class="form-check">
                    <input class="form-check-input" type="checkbox"
                           name="knowledge_items[]" value="{$item->id}"
                           id="know_{$item->id}"
                           {if in_array($item->id, $assignedKnowledgeIds|default:[])}checked{/if}>
                    <label class="form-check-label" for="know_{$item->id}">
                        {if $item->icon}<i class="fa {$item->icon} me-1"></i>{/if}
                        {$item->title|default:$item->slug}
                        {if !$item->is_active} <span class="badge bg-secondary">inaktiv</span>{/if}
                    </label>
                </div>
            {/foreach}
        {else}
            <p class="text-muted">Keine Einträge vorhanden. <a href="{$postURL}&bbf_page=knowledge&action=create">Eintrag erstellen</a></p>
        {/if}
    </div>
</div>
