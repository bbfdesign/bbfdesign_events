<div class="bbf-admin">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>{if $isEdit}Ticket-Kategorie bearbeiten{else}Neue Ticket-Kategorie{/if}</h2>
        <a href="?action=list" class="btn btn-outline-secondary"><i class="fa fa-arrow-left"></i> Zurück</a>
    </div>

    {if isset($smarty.get.msg)}
        <div class="alert alert-success alert-dismissible fade show">
            Kategorie {if $smarty.get.msg === 'created'}erstellt{else}aktualisiert{/if}.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    {/if}

    <form method="post" action="?action=save_category">
        <input type="hidden" name="category_id" value="{$category->id|default:0}">

        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <ul class="nav nav-tabs card-header-tabs" role="tablist">
                            {foreach $languages as $lang}
                                <li class="nav-item">
                                    <a class="nav-link{if $lang@first} active{/if}" data-bs-toggle="tab" href="#lang-tc-{$lang->iso}">{$lang->name}</a>
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
                                <div class="tab-pane fade{if $lang@first} show active{/if}" id="lang-tc-{$lang->iso}">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Name *</label>
                                        <input type="text" name="trans_{$lang->iso}_name" value="{$trans->name|default:''|escape:'html'}" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Beschreibung</label>
                                        <textarea name="trans_{$lang->iso}_description" class="form-control" rows="3">{$trans->description|default:''|escape:'html'}</textarea>
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
                            <input type="text" name="slug" value="{$category->slug|default:''|escape:'html'}" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Farbe</label>
                            <input type="color" name="color" value="{$category->color|default:'#3B82F6'}" class="form-control form-control-color">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Icon (CSS-Klasse)</label>
                            <input type="text" name="icon" value="{$category->icon|default:''|escape:'html'}" class="form-control" placeholder="z.B. fa-ticket-alt">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Sortierung</label>
                            <input type="number" name="sort_order" value="{$category->sort_order|default:0}" class="form-control" min="0">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Speichern</button>
        <a href="?action=list" class="btn btn-outline-secondary">Abbrechen</a>
    </form>
</div>
