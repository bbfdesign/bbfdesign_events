<div class="bbf-ticket-card{if $ticket->isSoldOut} bbf-ticket-card--sold-out{/if}">
    {if $ticket->category}
        <span class="bbf-ticket-card__category" style="background-color: {$ticket->category->color}">
            {$ticket->category->getName()}
        </span>
    {/if}

    <h4 class="bbf-ticket-card__name">{$ticket->getName()}</h4>

    {if $ticket->getDescription()}
        <p class="bbf-ticket-card__description">{$ticket->getDescription()}</p>
    {/if}

    {if $ticket->getDisplayPrice() !== null}
        <div class="bbf-ticket-card__price">
            {$ticket->getDisplayPrice()|number_format:2:',':'.'} {$ticket->currency}
        </div>
    {/if}

    {if $ticket->getHint()}
        <p class="bbf-ticket-card__hint">{$ticket->getHint()}</p>
    {/if}

    <div class="bbf-ticket-card__action">
        {if $ticket->isSoldOut}
            <span class="btn btn-secondary disabled">Ausverkauft</span>
        {elseif $ticket->isExternal() && $ticket->externalUrl}
            <a href="{$ticket->externalUrl}" target="_blank" rel="noopener" class="btn btn-primary">
                {$ticket->getCtaLabel()|default:'Tickets extern'}
            </a>
            {if $ticket->externalProvider}
                <small class="bbf-ticket-card__provider">via {$ticket->externalProvider}</small>
            {/if}
        {elseif $ticket->isWawiArticle() && $ticket->addToCartUrl}
            <a href="{$ticket->addToCartUrl}" class="btn btn-primary">
                {$ticket->getCtaLabel()|default:'In den Warenkorb'}
            </a>
        {else}
            <span class="btn btn-outline-secondary disabled">Nicht verfügbar</span>
        {/if}
    </div>
</div>
