{block name="bbf-events-listing"}
<section class="bbf-events-listing" aria-label="{if $isArchive}Veranstaltungsarchiv{else}Veranstaltungen{/if}">

    <div class="container">
        <div class="bbf-events-listing__header">
            <h1 class="bbf-events-listing__title">
                {if $isArchive}
                    Veranstaltungsarchiv
                {else}
                    Veranstaltungen
                {/if}
            </h1>
        </div>

        {include file="{$bbfEventsPath}components/filter_bar.tpl"}

        {if !empty($events)}
            <div class="bbf-events-grid row g-4" role="list">
                {foreach $events as $event}
                    <div class="col-md-6 col-lg-4">
                        {include file="{$bbfEventsPath}components/event_card.tpl" event=$event}
                    </div>
                {/foreach}
            </div>

            {if $pagination->totalPages > 1}
                {include file="{$bbfEventsPath}components/pagination.tpl"}
            {/if}
        {else}
            <div class="bbf-events-empty" role="status">
                <p>Keine Veranstaltungen gefunden.</p>
                {if $isArchive}
                    <a href="{$listingUrl}" class="btn btn-outline-primary">Kommende Veranstaltungen anzeigen</a>
                {/if}
            </div>
        {/if}
    </div>

</section>
{/block}
