<article class="bbf-event-card{if $event->isFeatured} bbf-event-card--featured{/if}" role="listitem" itemscope itemtype="https://schema.org/Event">
    <a href="{$event->url}" class="bbf-event-card__link" aria-label="{$event->getTitle()|escape:'html'}">

        <div class="bbf-event-card__image">
            {if $event->heroImage}
                <img src="{$event->heroImage}"
                     alt="{$event->getTitle()|escape:'html'}"
                     loading="lazy"
                     width="400" height="250"
                     class="bbf-event-card__img">
            {else}
                <div class="bbf-event-card__placeholder"></div>
            {/if}

            {include file="{$bbfEventsPath}components/event_status_badge.tpl" status=$event->computedStatus}
        </div>

        <div class="bbf-event-card__body">
            {include file="{$bbfEventsPath}components/event_dates.tpl" dates=$event->dates compact=true}

            <h3 class="bbf-event-card__title" itemprop="name">{$event->getTitle()}</h3>

            {if $event->getTeaser()}
                <p class="bbf-event-card__teaser" itemprop="description">
                    {$event->getTeaser()|truncate:120:'...'}
                </p>
            {/if}

            {if !empty($event->categories)}
                <div class="bbf-event-card__categories">
                    {foreach $event->categories as $cat}
                        <span class="bbf-event-card__category">{$cat->getName()}</span>
                    {/foreach}
                </div>
            {/if}
        </div>

    </a>
</article>
