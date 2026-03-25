<div class="bbf-marker-popup">
    {if $marker->image}
        <img src="{$marker->image}" alt="{$marker->getTitle()|escape:'html'}"
             class="bbf-marker-popup__img" style="width:100%;max-width:200px;border-radius:0.25rem;margin-bottom:0.5rem;">
    {/if}
    <strong class="bbf-marker-popup__title">{$marker->getTitle()}</strong>
    {if $marker->getDescription()}
        <p class="bbf-marker-popup__desc" style="font-size:0.8125rem;margin:0.25rem 0 0;">{$marker->getDescription()}</p>
    {/if}
    {if $marker->group}
        <span class="bbf-marker-popup__group" style="display:inline-block;margin-top:0.25rem;font-size:0.75rem;color:{$marker->group->color};">
            {$marker->group->getName()}
        </span>
    {/if}
</div>
