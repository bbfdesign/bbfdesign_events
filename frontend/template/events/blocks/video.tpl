<div class="bbf-video-block"
     data-video-source="{$videoSource|default:'youtube'}"
     data-video-url="{$videoUrl|default:''|escape:'html'}"
     data-video-poster="{$videoPoster|default:''|escape:'html'}"
     data-consent-required="{if $consentRequired|default:true}true{else}false{/if}">

    <div class="bbf-video-block__facade"
         style="position:relative;aspect-ratio:16/9;background:#000;border-radius:0.5rem;overflow:hidden;cursor:pointer;display:flex;align-items:center;justify-content:center;"
         {if $videoPoster}
            style="background-image:url('{$videoPoster}');background-size:cover;background-position:center;"
         {/if}>
        <div style="position:absolute;inset:0;background:rgba(0,0,0,0.3);"></div>
        <div style="position:relative;z-index:1;text-align:center;color:#fff;">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="currentColor" style="opacity:0.9;"><path d="M8 5v14l11-7z"/></svg>
            {if $consentRequired|default:true}
                <p style="font-size:0.8125rem;margin-top:0.5rem;opacity:0.8;">Klicken zum Laden des Videos</p>
            {/if}
        </div>
    </div>
</div>
