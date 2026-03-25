<div class="bbf-knowledge-card card h-100">
    {if $item->image}
        <img src="{$item->image}" alt="{$item->getTitle()|escape:'html'}" class="card-img-top" loading="lazy">
    {/if}
    <div class="card-body">
        {if $item->icon}
            <i class="fa {$item->icon} fa-2x text-primary mb-2"></i>
        {/if}
        <h5 class="card-title">{$item->getTitle()}</h5>
        {if $item->getTeaser()}
            <p class="card-text text-muted">{$item->getTeaser()}</p>
        {/if}
    </div>
    {if $item->translation && $item->translation->ctaUrl}
        <div class="card-footer bg-transparent border-0">
            <a href="{$item->translation->ctaUrl}" class="btn btn-sm btn-outline-primary">
                {$item->translation->ctaLabel|default:'Mehr erfahren'}
            </a>
        </div>
    {/if}
</div>
