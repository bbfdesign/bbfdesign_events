<section class="bbf-teaser-section">
    <div class="container">
        <h3 class="mb-4">Weitere Veranstaltungen</h3>
        <div class="row g-4" role="list">
            {* This block receives $teaserEvents from the PagebuilderService *}
            {if !empty($teaserEvents)}
                {foreach $teaserEvents as $teaserEvent}
                    <div class="col-md-6 col-lg-4">
                        {include file="{$bbfEventsPath}components/event_card.tpl" event=$teaserEvent}
                    </div>
                {/foreach}
            {/if}
        </div>
    </div>
</section>
