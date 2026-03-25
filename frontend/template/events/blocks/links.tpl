<section class="bbf-links-section">
    <div class="container">
        {if $title|default:''}<h3 class="mb-3">{$title}</h3>{/if}

        {if !empty($links)}
            <div class="bbf-links-list">
                {foreach $links as $link}
                    <div class="bbf-links-list__item d-flex align-items-center gap-3 py-2 border-bottom">
                        <div class="bbf-links-list__icon">
                            {if $link->linkType->value === 'external'}
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                            {elseif $link->linkType->value === 'product'}
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
                            {else}
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/></svg>
                            {/if}
                        </div>
                        <div class="flex-grow-1">
                            <a href="{$link->getUrl()}"
                               {if $link->linkType->isExternal()} target="_blank" rel="noopener"{/if}
                               class="bbf-links-list__link fw-bold text-decoration-none">
                                {$link->getLabel()|default:$link->getUrl()}
                            </a>
                            {if $link->translation && $link->translation->description}
                                <p class="mb-0 text-muted" style="font-size:0.8125rem;">{$link->translation->description}</p>
                            {/if}
                        </div>
                        {if $link->linkType->isExternal()}
                            <small class="text-muted">Extern</small>
                        {/if}
                    </div>
                {/foreach}
            </div>
        {/if}
    </div>
</section>
