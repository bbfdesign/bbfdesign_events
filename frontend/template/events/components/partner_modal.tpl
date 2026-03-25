<div class="modal fade" id="partner-modal-{$partner->id}" tabindex="-1" aria-labelledby="partner-modal-title-{$partner->id}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="partner-modal-title-{$partner->id}">{$partner->getName()}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Schließen"></button>
            </div>
            <div class="modal-body">
                {if $partner->logo}
                    <div class="text-center mb-4">
                        <img src="{$partner->logo}" alt="{$partner->getName()|escape:'html'}" class="img-fluid" style="max-height: 120px;">
                    </div>
                {/if}

                {if $partner->getLongDesc()}
                    <div class="bbf-partner-modal__content">
                        {$partner->getLongDesc() nofilter}
                    </div>
                {elseif $partner->getShortDesc()}
                    <p>{$partner->getShortDesc()}</p>
                {/if}

                {if !empty($partner->categories)}
                    <div class="mt-3">
                        {foreach $partner->categories as $cat}
                            <span class="badge bg-primary">{$cat->getName()}</span>
                        {/foreach}
                    </div>
                {/if}
            </div>
            <div class="modal-footer">
                {if $partner->translation && $partner->translation->ctaUrl}
                    <a href="{$partner->translation->ctaUrl}" target="_blank" rel="noopener" class="btn btn-primary">
                        {$partner->translation->ctaLabel|default:'Mehr erfahren'}
                    </a>
                {/if}
                {if $partner->websiteUrl}
                    <a href="{$partner->websiteUrl}" target="_blank" rel="noopener" class="btn btn-outline-secondary">Website besuchen</a>
                {/if}
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Schließen</button>
            </div>
        </div>
    </div>
</div>
