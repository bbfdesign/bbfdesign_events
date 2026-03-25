<div class="bbf-program-entry{if $entry->isHighlight} bbf-program-entry--highlight{/if}">
    <div class="d-flex gap-3">
        {if $entry->timeStart}
            <div class="bbf-program-entry__time text-muted" style="min-width: 80px; flex-shrink: 0;">
                {$entry->getTimeRange()}
            </div>
        {/if}
        <div class="bbf-program-entry__content flex-grow-1">
            <h5 class="bbf-program-entry__title mb-1">{$entry->getTitle()}</h5>
            {if $entry->getDescription()}
                <p class="bbf-program-entry__desc text-muted mb-1">{$entry->getDescription()}</p>
            {/if}
            <div class="d-flex align-items-center gap-2 flex-wrap">
                {if $entry->speakerName}
                    <span class="bbf-program-entry__speaker">
                        {if $entry->speakerImage}
                            <img src="{$entry->speakerImage}" alt="" width="24" height="24" class="rounded-circle me-1" loading="lazy">
                        {/if}
                        {$entry->speakerName}
                    </span>
                {/if}
                {if $entry->category}
                    <span class="badge" style="background-color: {$entry->category->color};">{$entry->category->getName()}</span>
                {/if}
                {if $entry->linkUrl}
                    <a href="{$entry->linkUrl}" target="{$entry->linkTarget}" class="btn btn-sm btn-link p-0">Details</a>
                {/if}
            </div>
        </div>
    </div>
</div>
