<div class="bbf-admin">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>{if $isEdit}Kategorie bearbeiten{else}Neue Kategorie{/if}</h2>
        <a href="{$postURL}&bbf_page=categories" class="btn btn-outline-secondary"><i class="fa fa-arrow-left"></i> Zurück</a>
    </div>

    {if isset($smarty.get.msg)}
        <div class="alert alert-success alert-dismissible fade show">
            Kategorie {if $smarty.get.msg === 'created'}erstellt{else}aktualisiert{/if}.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    {/if}

    <form method="post" action="{$postURL}&bbf_page=categories&action=save">
        {$jtl_token}
        <input type="hidden" name="category_id" value="{$category->id}">

        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <ul class="nav nav-tabs card-header-tabs" role="tablist">
                            {foreach $languages as $lang}
                                <li class="nav-item">
                                    <a class="nav-link{if $lang@first} active{/if}" data-bs-toggle="tab" href="#lang-cat-{$lang->iso}">{$lang->name}</a>
                                </li>
                            {/foreach}
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content">
                            {foreach $languages as $lang}
                                {assign var="trans" value=null}
                                {foreach $category->translations as $t}
                                    {if $t->languageIso === $lang->iso}{assign var="trans" value=$t}{/if}
                                {/foreach}
                                <div class="tab-pane fade{if $lang@first} show active{/if}" id="lang-cat-{$lang->iso}">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Name *</label>
                                        <input type="text" name="trans_{$lang->iso}_name" value="{$trans->name|default:''|escape:'html'}" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Beschreibung</label>
                                        <textarea name="trans_{$lang->iso}_description" class="form-control" rows="3">{$trans->description|default:''|escape:'html'}</textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Meta-Title</label>
                                        <input type="text" name="trans_{$lang->iso}_meta_title" value="{$trans->metaTitle|default:''|escape:'html'}" class="form-control">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Meta-Description</label>
                                        <textarea name="trans_{$lang->iso}_meta_description" class="form-control" rows="2">{$trans->metaDescription|default:''|escape:'html'}</textarea>
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
                            <input type="text" name="slug" value="{$category->slug|escape:'html'}" class="form-control" placeholder="Wird automatisch generiert">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Übergeordnete Kategorie</label>
                            <select name="parent_id" class="form-select">
                                <option value="">Keine (Hauptkategorie)</option>
                                {foreach $allCategories as $cat}
                                    {if $cat->id !== $category->id}
                                        <option value="{$cat->id}"{if $category->parentId === $cat->id} selected{/if}>{$cat->getName()|default:$cat->slug}</option>
                                    {/if}
                                {/foreach}
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Bild</label>
                            <input type="text" name="image" value="{$category->image|default:''|escape:'html'}" class="form-control" placeholder="Pfad zum Bild">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Sortierung</label>
                            <input type="number" name="sort_order" value="{$category->sortOrder}" class="form-control" min="0">
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" {if $category->isActive}checked{/if}>
                            <label class="form-check-label" for="is_active">Aktiv</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Speichern</button>
        <a href="{$postURL}&bbf_page=categories" class="btn btn-outline-secondary">Abbrechen</a>
    </form>
</div>
