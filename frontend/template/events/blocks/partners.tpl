<section class="bbf-partners-section">
    <div class="container">
        {if $displayMode === 'logo_grid'}
            <div class="row g-4 align-items-center justify-content-center">
                {foreach $partners as $partner}
                    <div class="col-6 col-md-{12/$columns}">
                        <div class="bbf-partner-logo text-center">
                            {if $partner->logo}
                                <img src="{$partner->logo}" alt="{$partner->getName()|escape:'html'}"
                                     class="img-fluid" style="max-height: 80px;" loading="lazy"
                                     {if $enableModal}data-bs-toggle="modal" data-bs-target="#partner-modal-{$partner->id}" role="button"{/if}>
                            {else}
                                <span class="fw-bold">{$partner->getName()}</span>
                            {/if}
                        </div>
                    </div>
                {/foreach}
            </div>
        {elseif $displayMode === 'cards'}
            <div class="row g-4">
                {foreach $partners as $partner}
                    <div class="col-md-6 col-lg-{12/$columns}">
                        <div class="card h-100">
                            {if $partner->logo}
                                <div class="card-img-top text-center p-3" style="background:#f8f9fa;">
                                    <img src="{$partner->logo}" alt="{$partner->getName()|escape:'html'}" class="img-fluid" style="max-height:60px;" loading="lazy">
                                </div>
                            {/if}
                            <div class="card-body">
                                <h5 class="card-title">{$partner->getName()}</h5>
                                {if $showDescription && $partner->getShortDesc()}
                                    <p class="card-text text-muted">{$partner->getShortDesc()}</p>
                                {/if}
                            </div>
                            {if $partner->websiteUrl}
                                <div class="card-footer bg-transparent">
                                    <a href="{$partner->websiteUrl}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary">Website</a>
                                </div>
                            {/if}
                        </div>
                    </div>
                {/foreach}
            </div>
        {/if}

        {* Partner Modals *}
        {if $enableModal}
            {foreach $partners as $partner}
                <div class="modal fade" id="partner-modal-{$partner->id}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">{$partner->getName()}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Schließen"></button>
                            </div>
                            <div class="modal-body">
                                {if $partner->logo}
                                    <div class="text-center mb-3"><img src="{$partner->logo}" alt="" class="img-fluid" style="max-height:100px;"></div>
                                {/if}
                                {if $partner->getLongDesc()}{$partner->getLongDesc() nofilter}{elseif $partner->getShortDesc()}<p>{$partner->getShortDesc()}</p>{/if}
                            </div>
                            {if $partner->websiteUrl || $partner->translation->ctaUrl}
                                <div class="modal-footer">
                                    {if $partner->translation->ctaUrl}
                                        <a href="{$partner->translation->ctaUrl}" target="_blank" rel="noopener" class="btn btn-primary">{$partner->translation->ctaLabel|default:'Mehr erfahren'}</a>
                                    {elseif $partner->websiteUrl}
                                        <a href="{$partner->websiteUrl}" target="_blank" rel="noopener" class="btn btn-outline-primary">Website besuchen</a>
                                    {/if}
                                </div>
                            {/if}
                        </div>
                    </div>
                </div>
            {/foreach}
        {/if}
    </div>
</section>
