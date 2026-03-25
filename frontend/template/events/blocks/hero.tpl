<div class="bbf-hero" style="{if $event->heroImage}background-image: url('{$event->heroImage}');{/if}">
    <div class="bbf-hero__overlay"></div>
    <div class="bbf-hero__content">
        <h1 class="bbf-hero__title">{$event->getTitle()}</h1>
        {if $event->getSubtitle()}
            <p class="bbf-hero__subtitle">{$event->getSubtitle()}</p>
        {/if}
        {if !empty($event->dates)}
            <div class="bbf-hero__dates">
                {include file="{$bbfEventsPath}components/event_dates.tpl" dates=$event->dates compact=false}
            </div>
        {/if}
    </div>
</div>
