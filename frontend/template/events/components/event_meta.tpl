<div class="bbf-event-meta">
    {if !empty($event->dates)}
        <div class="bbf-event-meta__item">
            <strong>Termin</strong>
            {include file="{$bbfEventsPath}components/event_dates.tpl" dates=$event->dates compact=false}
        </div>
    {/if}

    {if !empty($event->categories)}
        <div class="bbf-event-meta__item">
            <strong>Kategorien</strong>
            <div class="bbf-event-meta__categories">
                {foreach $event->categories as $cat}
                    <a href="/veranstaltungen/kategorie/{$cat->slug}" class="bbf-event-meta__category">
                        {$cat->getName()}
                    </a>
                {/foreach}
            </div>
        </div>
    {/if}

    {if $event->computedStatus}
        <div class="bbf-event-meta__item">
            <strong>Status</strong>
            {include file="{$bbfEventsPath}components/event_status_badge.tpl" status=$event->computedStatus}
            {if $event->computedStatus === 'upcoming'}
                <span class="bbf-status-badge bbf-status-badge--upcoming">Kommend</span>
            {/if}
        </div>
    {/if}
</div>
