{if !empty($dates)}
    <div class="bbf-event-dates{if $compact} bbf-event-dates--compact{/if}">
        {foreach $dates as $date}
            <time class="bbf-event-dates__item"
                  datetime="{$date->dateStart->format('Y-m-d')}"
                  itemprop="startDate"
                  content="{$date->dateStart->format('Y-m-d')}">

                {if $compact}
                    <span class="bbf-event-dates__day">{$date->dateStart->format('d')}</span>
                    <span class="bbf-event-dates__month">{$date->dateStart->format('m')}.{$date->dateStart->format('Y')}</span>
                {else}
                    <span class="bbf-event-dates__full">
                        {$date->dateStart->format('d.m.Y')}
                        {if $date->dateEnd && !$date->isSingleDay()}
                            – {$date->dateEnd->format('d.m.Y')}
                        {/if}
                    </span>

                    {if !$date->isAllday && !empty($date->timeSlots)}
                        {foreach $date->timeSlots as $slot}
                            <span class="bbf-event-dates__time">
                                {if $slot->label}{$slot->label}: {/if}
                                {$slot->getFormattedRange()}
                            </span>
                        {/foreach}
                    {/if}
                {/if}

            </time>
            {if !$date@last && $compact}
                <span class="bbf-event-dates__separator">,</span>
            {/if}
        {/foreach}
    </div>
{/if}
