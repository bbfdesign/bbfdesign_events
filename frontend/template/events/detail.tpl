{block name="bbf-events-detail"}

{$schemaJsonLd nofilter}

{if $pageBuilderCss}
    <style>{$pageBuilderCss nofilter}</style>
{/if}

<article class="bbf-event-detail" itemscope itemtype="https://schema.org/Event">
    <meta itemprop="name" content="{$event->getTitle()|escape:'html'}">
    <meta itemprop="description" content="{$event->getTeaser()|escape:'html'}">

    {if $pageBuilderHtml}
        {* Pagebuilder output with dynamic blocks already replaced *}
        <div class="bbf-event-detail__content">
            {$pageBuilderHtml nofilter}
        </div>
    {else}
        {* Fallback: static detail layout when no pagebuilder data exists *}
        {if $event->heroImage}
            <div class="bbf-event-detail__hero">
                <img src="{$event->heroImage}" alt="{$event->getTitle()|escape:'html'}"
                     class="bbf-event-detail__hero-img" width="1200" height="600">
                <div class="bbf-event-detail__hero-overlay">
                    <div class="container">
                        <h1 class="bbf-event-detail__title">{$event->getTitle()}</h1>
                        {if $event->getSubtitle()}
                            <p class="bbf-event-detail__subtitle">{$event->getSubtitle()}</p>
                        {/if}
                        {include file="{$bbfEventsPath}components/event_dates.tpl" dates=$event->dates compact=false}
                    </div>
                </div>
            </div>
        {else}
            <div class="container">
                <h1 class="bbf-event-detail__title">{$event->getTitle()}</h1>
                {if $event->getSubtitle()}
                    <p class="bbf-event-detail__subtitle">{$event->getSubtitle()}</p>
                {/if}
            </div>
        {/if}

        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    {if $event->getTeaser()}
                        <div class="bbf-event-detail__teaser">
                            <p class="lead">{$event->getTeaser()}</p>
                        </div>
                    {/if}

                    {if $event->getDescription()}
                        <div class="bbf-event-detail__description">
                            {$event->getDescription() nofilter}
                        </div>
                    {/if}
                </div>

                <div class="col-lg-4">
                    <aside class="bbf-event-detail__sidebar">
                        {include file="{$bbfEventsPath}components/event_meta.tpl" event=$event}

                        {if !empty($tickets)}
                            <div class="bbf-event-detail__tickets-sidebar">
                                <h3>Tickets</h3>
                                {foreach $tickets as $ticket}
                                    {include file="{$bbfEventsPath}components/ticket_card.tpl" ticket=$ticket}
                                {/foreach}
                            </div>
                        {/if}

                        {include file="{$bbfEventsPath}components/share_buttons.tpl" event=$event}
                    </aside>
                </div>
            </div>
        </div>
    {/if}

</article>
{/block}
