<section class="bbf-tickets-section">
    <div class="container">
        {if $displayMode === 'cards'}
            <div class="row g-4">
                {foreach $tickets as $ticket}
                    <div class="col-md-6 col-lg-4">
                        {include file="{$bbfEventsPath}components/ticket_card.tpl" ticket=$ticket}
                    </div>
                {/foreach}
            </div>
        {elseif $displayMode === 'table'}
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Ticket</th>
                            {if $showDescription}<th>Beschreibung</th>{/if}
                            {if $showPrice}<th>Preis</th>{/if}
                            {if $showAvailability}<th>Status</th>{/if}
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        {foreach $tickets as $ticket}
                            <tr{if $ticket->isSoldOut} class="text-muted"{/if}>
                                <td class="fw-bold">{$ticket->getName()}</td>
                                {if $showDescription}<td>{$ticket->getDescription()|truncate:80}</td>{/if}
                                {if $showPrice}<td>{if $ticket->getDisplayPrice() !== null}{$ticket->getDisplayPrice()|number_format:2:',':'.'} {$ticket->currency}{else}-{/if}</td>{/if}
                                {if $showAvailability}<td>{if $ticket->isSoldOut}<span class="badge bg-secondary">Ausverkauft</span>{elseif $ticket->isAvailable()}<span class="badge bg-success">Verfügbar</span>{/if}</td>{/if}
                                <td>
                                    {if $ticket->isSoldOut}
                                        <span class="btn btn-sm btn-secondary disabled">Ausverkauft</span>
                                    {elseif $ticket->isExternal() && $ticket->externalUrl}
                                        <a href="{$ticket->externalUrl}" target="_blank" rel="noopener" class="btn btn-sm btn-primary">{$ticket->getCtaLabel()|default:'Tickets'}</a>
                                    {elseif $ticket->addToCartUrl}
                                        <a href="{$ticket->addToCartUrl}" class="btn btn-sm btn-primary">{$ticket->getCtaLabel()|default:'Kaufen'}</a>
                                    {/if}
                                </td>
                            </tr>
                        {/foreach}
                    </tbody>
                </table>
            </div>
        {else}
            {* compact *}
            {foreach $tickets as $ticket}
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <div>
                        <strong>{$ticket->getName()}</strong>
                        {if $showPrice && $ticket->getDisplayPrice() !== null}
                            <span class="ms-2 text-muted">{$ticket->getDisplayPrice()|number_format:2:',':'.'} {$ticket->currency}</span>
                        {/if}
                    </div>
                    {if !$ticket->isSoldOut && $ticket->addToCartUrl}
                        <a href="{$ticket->addToCartUrl}" class="btn btn-sm btn-primary">{$ticket->getCtaLabel()|default:'Kaufen'}</a>
                    {elseif $ticket->isSoldOut}
                        <span class="badge bg-secondary">Ausverkauft</span>
                    {/if}
                </div>
            {/foreach}
        {/if}
    </div>
</section>
