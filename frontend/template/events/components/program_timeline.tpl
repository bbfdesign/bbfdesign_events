<div class="bbf-program-timeline">
    {foreach $entries as $entry}
        <div class="bbf-program-timeline__item{if $entry->isHighlight} bbf-program-timeline__item--highlight{/if}">
            <div class="bbf-program-timeline__marker">
                {if $entry->category}
                    <span class="bbf-program-timeline__dot" style="background-color: {$entry->category->color};"></span>
                {else}
                    <span class="bbf-program-timeline__dot"></span>
                {/if}
            </div>
            <div class="bbf-program-timeline__body">
                {if $entry->timeStart}
                    <time class="bbf-program-timeline__time">{$entry->getTimeRange()}</time>
                {/if}
                <h4 class="bbf-program-timeline__title">{$entry->getTitle()}</h4>
                {if $entry->getDescription()}
                    <p class="bbf-program-timeline__desc">{$entry->getDescription()}</p>
                {/if}
                {if $entry->speakerName}
                    <div class="bbf-program-timeline__speaker">
                        {if $entry->speakerImage}
                            <img src="{$entry->speakerImage}" alt="{$entry->speakerName|escape:'html'}" width="32" height="32" class="rounded-circle" loading="lazy">
                        {/if}
                        <span>{$entry->speakerName}</span>
                        {if $entry->getSpeakerTitle()} <small class="text-muted">– {$entry->getSpeakerTitle()}</small>{/if}
                    </div>
                {/if}
                {if $entry->category}
                    <span class="badge" style="background-color: {$entry->category->color}; color: #fff;">{$entry->category->getName()}</span>
                {/if}
            </div>
        </div>
    {/foreach}
</div>
