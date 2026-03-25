<section class="bbf-program-section">
    <div class="container">
        {if $displayMode === 'timeline'}
            <div class="bbf-program-timeline">
                {foreach $programEntries as $entry}
                    <div class="bbf-program-timeline__item{if $entry->isHighlight} bbf-program-timeline__item--highlight{/if}">
                        {if $entry->timeStart}
                            <div class="bbf-program-timeline__time">
                                {$entry->getTimeRange()}
                            </div>
                        {/if}
                        <div class="bbf-program-timeline__content">
                            {if $entry->category && $showCategories}
                                <span class="bbf-program-timeline__category" style="background-color: {$entry->category->color}">
                                    {$entry->category->getName()}
                                </span>
                            {/if}
                            <h4 class="bbf-program-timeline__title">{$entry->getTitle()}</h4>
                            {if $entry->getDescription()}
                                <p class="bbf-program-timeline__desc">{$entry->getDescription()}</p>
                            {/if}
                            {if $showSpeakers && $entry->speakerName}
                                <div class="bbf-program-timeline__speaker">
                                    {if $entry->speakerImage}
                                        <img src="{$entry->speakerImage}" alt="{$entry->speakerName}" class="bbf-program-timeline__speaker-img" width="40" height="40" loading="lazy">
                                    {/if}
                                    <div>
                                        <strong>{$entry->speakerName}</strong>
                                        {if $entry->getSpeakerTitle()}<br><small class="text-muted">{$entry->getSpeakerTitle()}</small>{/if}
                                    </div>
                                </div>
                            {/if}
                        </div>
                    </div>
                {/foreach}
            </div>
        {elseif $displayMode === 'grid'}
            <div class="row g-4">
                {foreach $programEntries as $entry}
                    <div class="col-md-6 col-lg-4">
                        <div class="bbf-program-card">
                            {if $entry->category && $showCategories}
                                <span class="badge" style="background-color: {$entry->category->color}">{$entry->category->getName()}</span>
                            {/if}
                            <h4>{$entry->getTitle()}</h4>
                            {if $entry->timeStart}<p class="text-muted">{$entry->getTimeRange()}</p>{/if}
                            {if $entry->getDescription()}<p>{$entry->getDescription()|truncate:120}</p>{/if}
                        </div>
                    </div>
                {/foreach}
            </div>
        {else}
            {* list mode *}
            <div class="bbf-program-list">
                {foreach $programEntries as $entry}
                    <div class="bbf-program-list__item d-flex gap-3 py-3 border-bottom">
                        {if $entry->timeStart}
                            <div class="bbf-program-list__time text-muted" style="min-width:80px;">{$entry->getTimeRange()}</div>
                        {/if}
                        <div>
                            <strong>{$entry->getTitle()}</strong>
                            {if $entry->speakerName && $showSpeakers} <span class="text-muted">– {$entry->speakerName}</span>{/if}
                        </div>
                    </div>
                {/foreach}
            </div>
        {/if}
    </div>
</section>
