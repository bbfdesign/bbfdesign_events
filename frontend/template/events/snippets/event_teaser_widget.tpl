{* Event Teaser Widget – for homepage or sidebar use *}
{if !empty($teaserEvents)}
<div class="bbf-teaser-widget">
    {if $widgetTitle|default:''}
        <h3 class="bbf-teaser-widget__title">{$widgetTitle}</h3>
    {/if}
    <div class="bbf-teaser-widget__list">
        {foreach $teaserEvents as $event}
            <a href="{$event->url}" class="bbf-teaser-widget__item">
                {if $event->heroImage}
                    <img src="{$event->heroImage}" alt="{$event->getTitle()|escape:'html'}"
                         class="bbf-teaser-widget__img" loading="lazy" width="80" height="80">
                {/if}
                <div class="bbf-teaser-widget__body">
                    <strong class="bbf-teaser-widget__name">{$event->getTitle()}</strong>
                    {if !empty($event->dates)}
                        <small class="bbf-teaser-widget__date">{$event->dates[0]->dateStart->format('d.m.Y')}</small>
                    {/if}
                </div>
            </a>
        {/foreach}
    </div>
    <a href="/veranstaltungen" class="bbf-teaser-widget__more">Alle Veranstaltungen &rarr;</a>
</div>
{/if}
