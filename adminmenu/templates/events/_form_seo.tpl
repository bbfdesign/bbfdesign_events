<div class="card">
    <div class="card-header">
        <ul class="nav nav-tabs card-header-tabs" role="tablist">
            {foreach $languages as $lang}
                <li class="nav-item">
                    <a class="nav-link{if $lang@first} active{/if}"
                       data-bs-toggle="tab" href="#lang-seo-{$lang.iso}">
                        {$lang.name} ({$lang.iso|upper})
                    </a>
                </li>
            {/foreach}
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content">
            {foreach $languages as $lang}
                {assign var="prefix" value="trans_{$lang.iso}_"}
                {assign var="trans" value=null}
                {foreach $event->translations as $t}
                    {if $t->languageIso === $lang.iso}
                        {assign var="trans" value=$t}
                    {/if}
                {/foreach}

                <div class="tab-pane fade{if $lang@first} show active{/if}" id="lang-seo-{$lang.iso}">
                    <div class="mb-3">
                        <label class="form-label">Lokalisierter Slug</label>
                        <div class="input-group">
                            <span class="input-group-text">/veranstaltungen/</span>
                            <input type="text" name="{$prefix}slug_localized"
                                   value="{$trans->slugLocalized|default:''|escape:'html'}"
                                   class="form-control" placeholder="Optional: sprachspezifischer URL-Slug">
                        </div>
                    </div>

                    <h6 class="mt-4 mb-3">Meta-Daten</h6>
                    <div class="mb-3">
                        <label class="form-label">Meta-Title</label>
                        <input type="text" name="{$prefix}meta_title"
                               value="{$trans->metaTitle|default:''|escape:'html'}"
                               class="form-control" maxlength="70">
                        <div class="form-text">Empfohlen: max. 60 Zeichen</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Meta-Description</label>
                        <textarea name="{$prefix}meta_description" class="form-control" rows="2" maxlength="160">{$trans->metaDescription|default:''|escape:'html'}</textarea>
                        <div class="form-text">Empfohlen: max. 155 Zeichen</div>
                    </div>

                    <h6 class="mt-4 mb-3">Open Graph</h6>
                    <div class="mb-3">
                        <label class="form-label">OG Title</label>
                        <input type="text" name="{$prefix}og_title"
                               value="{$trans->ogTitle|default:''|escape:'html'}"
                               class="form-control" placeholder="Überschreibt den Standard-Titel">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">OG Description</label>
                        <textarea name="{$prefix}og_description" class="form-control" rows="2">{$trans->ogDescription|default:''|escape:'html'}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">OG Image</label>
                        <input type="text" name="{$prefix}og_image"
                               value="{$trans->ogImage|default:''|escape:'html'}"
                               class="form-control" placeholder="Pfad zum OG-Bild">
                    </div>
                </div>
            {/foreach}
        </div>
    </div>
</div>
