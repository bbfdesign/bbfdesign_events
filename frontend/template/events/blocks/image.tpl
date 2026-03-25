<div class="bbf-image-block">
    {if $imageSrc}
        <figure class="bbf-image-block__figure">
            <img src="{$imageSrc}" alt="{$imageAlt|default:''|escape:'html'}"
                 class="bbf-image-block__img img-fluid" loading="lazy"
                 {if $imageWidth}width="{$imageWidth}"{/if}
                 {if $imageHeight}height="{$imageHeight}"{/if}>
            {if $imageCaption|default:''}
                <figcaption class="bbf-image-block__caption text-muted mt-2">{$imageCaption}</figcaption>
            {/if}
        </figure>
    {/if}
</div>
