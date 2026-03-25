{block name="bbf-events-category"}
<section class="bbf-events-listing bbf-events-listing--category" aria-label="{$category->getName()}">

    <div class="container">
        <div class="bbf-events-listing__header">
            <h1 class="bbf-events-listing__title">{$category->getName()}</h1>
            {if $category->getDescription()}
                <p class="bbf-events-listing__description">{$category->getDescription()}</p>
            {/if}
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
                <p>Keine Veranstaltungen in dieser Kategorie gefunden.</p>
                <a href="{$listingUrl}" class="btn btn-outline-primary">Alle Veranstaltungen anzeigen</a>
            </div>
        {/if}
    </div>

</section>
{/block}
