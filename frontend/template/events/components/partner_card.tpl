<div class="bbf-partner-card">
    {if $partner->logo}
        <div class="bbf-partner-card__logo">
            <img src="{$partner->logo}" alt="{$partner->getName()|escape:'html'}" class="img-fluid" loading="lazy">
        </div>
    {/if}
    <div class="bbf-partner-card__body">
        <h5 class="bbf-partner-card__name">{$partner->getName()}</h5>
        {if $partner->getShortDesc()}
            <p class="bbf-partner-card__desc">{$partner->getShortDesc()}</p>
        {/if}
        {if !empty($partner->categories)}
            <div class="bbf-partner-card__categories">
                {foreach $partner->categories as $cat}
                    <span class="badge bg-light text-dark">{$cat->getName()}</span>
                {/foreach}
            </div>
        {/if}
    </div>
    {if $partner->websiteUrl}
        <div class="bbf-partner-card__footer">
            <a href="{$partner->websiteUrl}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary">Website</a>
        </div>
    {/if}
</div>
