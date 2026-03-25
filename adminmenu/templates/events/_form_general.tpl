<div class="row">
    <div class="col-lg-8">
        {* Sprach-Tabs für Übersetzungen *}
        <div class="card mb-4">
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs" role="tablist">
                    {foreach $languages as $lang}
                        <li class="nav-item">
                            <a class="nav-link{if $lang@first} active{/if}"
                               data-bs-toggle="tab"
                               href="#lang-general-{$lang.iso}">
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

                        <div class="tab-pane fade{if $lang@first} show active{/if}"
                             id="lang-general-{$lang.iso}">

                            <div class="mb-3">
                                <label class="form-label fw-bold">Titel *</label>
                                <input type="text" name="{$prefix}title"
                                       value="{$trans->title|default:''|escape:'html'}"
                                       class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Untertitel</label>
                                <input type="text" name="{$prefix}subtitle"
                                       value="{$trans->subtitle|default:''|escape:'html'}"
                                       class="form-control">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Teaser</label>
                                <textarea name="{$prefix}teaser" class="form-control" rows="3">{$trans->teaser|default:''|escape:'html'}</textarea>
                                <div class="form-text">Kurzbeschreibung für Listing-Karten (max. 200 Zeichen empfohlen)</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Beschreibung</label>
                                <textarea name="{$prefix}description" class="form-control" rows="10">{$trans->description|default:''|escape:'html'}</textarea>
                                <div class="form-text">HTML erlaubt. Wird im Fallback-Detail-Template angezeigt.</div>
                            </div>
                        </div>
                    {/foreach}
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        {* Status & Einstellungen *}
        <div class="card mb-4">
            <div class="card-header"><strong>Status & Veröffentlichung</strong></div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        {foreach $statuses as $s}
                            <option value="{$s->value}"{if $event->status === $s} selected{/if}>
                                {$s->label()}
                            </option>
                        {/foreach}
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Veröffentlichen ab</label>
                    <input type="datetime-local" name="publish_from"
                           value="{if $event->publishFrom}{$event->publishFrom->format('Y-m-d\TH:i')}{/if}"
                           class="form-control">
                </div>

                <div class="mb-3">
                    <label class="form-label">Veröffentlichen bis</label>
                    <input type="datetime-local" name="publish_to"
                           value="{if $event->publishTo}{$event->publishTo->format('Y-m-d\TH:i')}{/if}"
                           class="form-control">
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="is_featured"
                           id="is_featured" {if $event->isFeatured}checked{/if}>
                    <label class="form-check-label" for="is_featured">Als Featured markieren</label>
                </div>
            </div>
        </div>

        {* Event-Typ *}
        <div class="card mb-4">
            <div class="card-header"><strong>Event-Typ</strong></div>
            <div class="card-body">
                <select name="event_type" class="form-select">
                    {foreach $eventTypes as $type}
                        <option value="{$type->value}"{if $event->eventType === $type} selected{/if}>
                            {$type->label()}
                        </option>
                    {/foreach}
                </select>
            </div>
        </div>

        {* Hero-Bild *}
        <div class="card mb-4">
            <div class="card-header"><strong>Hero-Bild</strong></div>
            <div class="card-body">
                <input type="text" name="hero_image"
                       value="{$event->heroImage|default:''|escape:'html'}"
                       class="form-control" placeholder="/mediafiles/bbfdesign_events/images/...">
                <div class="form-text">Pfad zum Hero-Bild</div>
                {if $event->heroImage}
                    <img src="{$event->heroImage}" alt="Hero Preview" class="img-fluid mt-2 rounded" style="max-height: 150px;">
                {/if}
            </div>
        </div>

        {* Slug & Sortierung *}
        <div class="card mb-4">
            <div class="card-header"><strong>URL & Sortierung</strong></div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Slug (URL)</label>
                    <div class="input-group">
                        <span class="input-group-text">/veranstaltungen/</span>
                        <input type="text" name="slug"
                               value="{$event->slug|escape:'html'}"
                               class="form-control" placeholder="wird automatisch generiert">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Sortierung</label>
                    <input type="number" name="sort_order"
                           value="{$event->sortOrder}"
                           class="form-control" min="0">
                </div>
            </div>
        </div>
    </div>
</div>
