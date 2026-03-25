<div class="bbf-gallery-block">
    {if !empty($galleryImages)}
        <div class="bbf-gallery row g-3" data-lightbox="true">
            {foreach $galleryImages as $img}
                <div class="col-6 col-md-{12/$columns|default:3}">
                    <a href="{$img.url}" class="bbf-gallery__item d-block overflow-hidden rounded" style="aspect-ratio:4/3;">
                        <img src="{$img.url}" alt="{$img.alt|default:''|escape:'html'}"
                             class="w-100 h-100" style="object-fit:cover;" loading="lazy">
                    </a>
                </div>
            {/foreach}
        </div>
    {/if}
</div>
