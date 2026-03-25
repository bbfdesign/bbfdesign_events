{* Upcoming Events Snippet – shows next N events *}
{if !empty($upcomingEvents)}
<section class="bbf-upcoming-events">
    <div class="container">
        <h2 class="bbf-upcoming-events__title">Kommende Veranstaltungen</h2>
        <div class="row g-4">
            {foreach $upcomingEvents as $event}
                <div class="col-md-6 col-lg-4">
                    {include file="{$bbfEventsPath}components/event_card.tpl" event=$event}
                </div>
            {/foreach}
        </div>
        <div class="text-center mt-4">
            <a href="/veranstaltungen" class="btn btn-outline-primary">Alle Veranstaltungen anzeigen</a>
        </div>
    </div>
</section>
{/if}
