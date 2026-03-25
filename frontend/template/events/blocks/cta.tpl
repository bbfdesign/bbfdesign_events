<div class="bbf-cta-block">
    <div class="bbf-cta-block__inner text-center py-5">
        {if $title}<h2>{$title}</h2>{/if}
        {if $description}<p class="lead">{$description}</p>{/if}
        {if $ctaUrl}
            <a href="{$ctaUrl}" class="btn btn-primary btn-lg"{if $ctaTarget} target="{$ctaTarget}"{/if}>
                {$ctaLabel|default:'Mehr erfahren'}
            </a>
        {/if}
    </div>
</div>
