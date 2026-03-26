<div class="bbf-admin">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>{if $isEdit}Wissenswertes bearbeiten{else}Neuer Eintrag{/if}</h2>
        <a href="{$postURL}&bbf_page=knowledge" class="btn btn-outline-secondary"><i class="fa fa-arrow-left"></i> Zurück</a>
    </div>

    {if isset($smarty.get.msg)}
        <div class="alert alert-success alert-dismissible fade show">
            Eintrag {if $smarty.get.msg === 'created'}erstellt{else}aktualisiert{/if}.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    {/if}

    <form method="post" action="{$postURL}&bbf_page=knowledge&action=save">
        {$jtl_token}
        <input type="hidden" name="item_id" value="{$item->id|default:0}">

        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <ul class="nav nav-tabs card-header-tabs" role="tablist">
                            {foreach $languages as $lang}
                                <li class="nav-item">
                                    <a class="nav-link{if $lang@first} active{/if}" data-bs-toggle="tab" href="#lang-k-{$lang->iso}">{$lang->name}</a>
                                </li>
                            {/foreach}
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content">
                            {foreach $languages as $lang}
                                {assign var="trans" value=null}
                                {foreach $translations as $t}
                                    {if $t->language_iso === $lang->iso}{assign var="trans" value=$t}{/if}
                                {/foreach}
                                <div class="tab-pane fade{if $lang@first} show active{/if}" id="lang-k-{$lang->iso}">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Titel *</label>
                                        <input type="text" name="trans_{$lang->iso}_title" value="{$trans->title|default:''|escape:'html'}" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Teaser</label>
                                        <textarea name="trans_{$lang->iso}_teaser" class="form-control" rows="2">{$trans->teaser|default:''|escape:'html'}</textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Inhalt</label>
                                        <textarea name="trans_{$lang->iso}_content" class="form-control" rows="8">{$trans->content|default:''|escape:'html'}</textarea>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">CTA Label</label>
                                            <input type="text" name="trans_{$lang->iso}_cta_label" value="{$trans->cta_label|default:''|escape:'html'}" class="form-control">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">CTA URL</label>
                                            <input type="url" name="trans_{$lang->iso}_cta_url" value="{$trans->cta_url|default:''|escape:'html'}" class="form-control">
                                        </div>
                                    </div>
                                </div>
                            {/foreach}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card-header"><strong>Einstellungen</strong></div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Slug</label>
                            <input type="text" name="slug" value="{$item->slug|default:''|escape:'html'}" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Bild</label>
                            <input type="text" name="image" value="{$item->image|default:''|escape:'html'}" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Icon (CSS-Klasse)</label>
                            <input type="text" name="icon" value="{$item->icon|default:''|escape:'html'}" class="form-control" placeholder="z.B. fa-info-circle">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Sortierung</label>
                            <input type="number" name="sort_order" value="{$item->sort_order|default:0}" class="form-control" min="0">
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" {if !$isEdit || $item->is_active}checked{/if}>
                            <label class="form-check-label" for="is_active">Aktiv</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Speichern</button>
        <a href="{$postURL}&bbf_page=knowledge" class="btn btn-outline-secondary">Abbrechen</a>
    </form>
</div>
