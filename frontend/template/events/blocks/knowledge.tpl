<section class="bbf-knowledge-section">
    <div class="container">
        {if $displayMode === 'cards'}
            <div class="row g-4">
                {foreach $knowledgeItems as $item}
                    <div class="col-md-{12/$columns}">
                        <div class="card h-100">
                            {if $showImage && $item->image}
                                <img src="{$item->image}" alt="{$item->getTitle()|escape:'html'}" class="card-img-top" loading="lazy">
                            {/if}
                            <div class="card-body">
                                {if $item->icon}<i class="fa {$item->icon} fa-2x mb-2 text-primary"></i>{/if}
                                <h5 class="card-title">{$item->getTitle()}</h5>
                                {if $item->getTeaser()}<p class="card-text text-muted">{$item->getTeaser()}</p>{/if}
                            </div>
                            {if $showCta && $item->translation->ctaUrl}
                                <div class="card-footer bg-transparent">
                                    <a href="{$item->translation->ctaUrl}" class="btn btn-sm btn-outline-primary">{$item->translation->ctaLabel|default:'Mehr erfahren'}</a>
                                </div>
                            {/if}
                        </div>
                    </div>
                {/foreach}
            </div>
        {elseif $displayMode === 'accordion'}
            <div class="accordion" id="bbf-knowledge-acc">
                {foreach $knowledgeItems as $item}
                    <div class="accordion-item">
                        <h3 class="accordion-header">
                            <button class="accordion-button{if !$item@first} collapsed{/if}" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#know-{$item->id}">
                                {if $item->icon}<i class="fa {$item->icon} me-2"></i>{/if}
                                {$item->getTitle()}
                            </button>
                        </h3>
                        <div id="know-{$item->id}" class="accordion-collapse collapse{if $item@first} show{/if}" data-bs-parent="#bbf-knowledge-acc">
                            <div class="accordion-body">
                                {if $item->getContent()}{$item->getContent() nofilter}{else}{$item->getTeaser()}{/if}
                            </div>
                        </div>
                    </div>
                {/foreach}
            </div>
        {else}
            {* list *}
            {foreach $knowledgeItems as $item}
                <div class="d-flex gap-3 py-3 border-bottom">
                    {if $showImage && $item->image}
                        <img src="{$item->image}" alt="" style="width:60px;height:60px;object-fit:cover;border-radius:0.5rem;" loading="lazy">
                    {/if}
                    <div>
                        <h5 class="mb-1">{$item->getTitle()}</h5>
                        {if $item->getTeaser()}<p class="text-muted mb-0">{$item->getTeaser()}</p>{/if}
                    </div>
                </div>
            {/foreach}
        {/if}
    </div>
</section>
