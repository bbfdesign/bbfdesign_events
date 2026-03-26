<div class="bbf-admin">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>{if $isEdit}Karte bearbeiten{else}Neue Karte{/if}</h2>
        <a href="{$postURL}&bbf_page=areas" class="btn btn-outline-secondary"><i class="fa fa-arrow-left"></i> Zurück</a>
    </div>

    {if isset($smarty.get.msg)}
        <div class="alert alert-success alert-dismissible fade show">
            {if $smarty.get.msg === 'created'}Karte erstellt.
            {elseif $smarty.get.msg === 'updated'}Karte aktualisiert.
            {elseif $smarty.get.msg === 'marker_saved'}Marker gespeichert.
            {elseif $smarty.get.msg === 'marker_deleted'}Marker gelöscht.
            {elseif $smarty.get.msg === 'group_saved'}Gruppe gespeichert.
            {elseif $smarty.get.msg === 'group_deleted'}Gruppe gelöscht.
            {/if}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    {/if}

    {* Karten-Stammdaten *}
    <form method="post" action="{$postURL}&bbf_page=areas&action=save">
        {$jtl_token}
        <input type="hidden" name="map_id" value="{$map->id|default:0}">

        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <ul class="nav nav-tabs card-header-tabs" role="tablist">
                            {foreach $languages as $lang}
                                <li class="nav-item">
                                    <a class="nav-link{if $lang@first} active{/if}" data-bs-toggle="tab" href="#lang-area-{$lang->iso}">{$lang->name}</a>
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
                                <div class="tab-pane fade{if $lang@first} show active{/if}" id="lang-area-{$lang->iso}">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Titel *</label>
                                        <input type="text" name="trans_{$lang->iso}_title" value="{$trans->title|default:''|escape:'html'}" class="form-control" required>
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
                            <input type="text" name="slug" value="{$map->slug|default:''|escape:'html'}" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kartentyp</label>
                            <select name="map_type" class="form-select">
                                {foreach $mapTypes as $val => $label}
                                    <option value="{$val}"{if ($map->map_type|default:'interactive') === $val} selected{/if}>{$label}</option>
                                {/foreach}
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Statisches Bild</label>
                            <input type="text" name="static_image" value="{$map->static_image|default:''|escape:'html'}" class="form-control" placeholder="Pfad zum Hintergrundbild">
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label">Latitude</label>
                                <input type="number" step="0.0000001" name="center_lat" value="{$map->center_lat|default:''}" class="form-control">
                            </div>
                            <div class="col-6">
                                <label class="form-label">Longitude</label>
                                <input type="number" step="0.0000001" name="center_lng" value="{$map->center_lng|default:''}" class="form-control">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Zoom-Level</label>
                            <input type="number" name="zoom_level" value="{$map->zoom_level|default:14}" class="form-control" min="1" max="20">
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" {if !$isEdit || $map->is_active}checked{/if}>
                            <label class="form-check-label" for="is_active">Aktiv</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Karte speichern</button>
    </form>

    {if $isEdit}
        <hr class="my-5">

        {* Marker-Gruppen *}
        <div id="groups">
            <h4 class="mb-3">Marker-Gruppen</h4>
            {if !empty($groups)}
                <div class="row g-3 mb-3">
                    {foreach $groups as $group}
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="d-inline-block rounded-circle me-2" style="width:16px;height:16px;background:{$group->color};"></span>
                                        <strong>{$group->name|default:'(ohne Name)'}</strong>
                                        {if $group->icon} <i class="fa {$group->icon} ms-1"></i>{/if}
                                    </div>
                                    <a href="{$postURL}&bbf_page=areas&action=delete_group&group_id={$group->id}&map_id={$map->id}" class="btn btn-sm btn-outline-danger" onclick="return confirm('Gruppe löschen?')"><i class="fa fa-times"></i></a>
                                </div>
                            </div>
                        </div>
                    {/foreach}
                </div>
            {/if}

            <div class="card mb-4">
                <div class="card-header"><strong>Neue Gruppe</strong></div>
                <div class="card-body">
                    <form method="post" action="{$postURL}&bbf_page=areas&action=save_group" class="row g-3 align-items-end">
                        {$jtl_token}
                        <input type="hidden" name="map_id" value="{$map->id}">
                        <input type="hidden" name="group_id" value="0">
                        <div class="col-md-3">
                            <label class="form-label">Name (DE)</label>
                            <input type="text" name="group_trans_ger_name" class="form-control" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Farbe</label>
                            <input type="color" name="group_color" value="#EF4444" class="form-control form-control-color">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Icon</label>
                            <input type="text" name="group_icon" class="form-control" placeholder="fa-star">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Sort</label>
                            <input type="number" name="group_sort_order" value="0" class="form-control" min="0">
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-outline-primary"><i class="fa fa-plus"></i> Gruppe hinzufügen</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {* Marker *}
        <div id="markers">
            <h4 class="mb-3">Marker</h4>
            {if !empty($markers)}
                <div class="table-responsive mb-3">
                    <table class="table table-sm">
                        <thead>
                            <tr><th>Titel</th><th>Gruppe</th><th>Lat/Lng</th><th>Pos X/Y</th><th width="80">Aktionen</th></tr>
                        </thead>
                        <tbody>
                            {foreach $markers as $marker}
                                <tr>
                                    <td>{$marker->title|default:'(kein Titel)'}</td>
                                    <td>{if $marker->group_name}<span class="d-inline-block rounded-circle me-1" style="width:10px;height:10px;background:{$marker->group_color};"></span>{$marker->group_name}{else}-{/if}</td>
                                    <td><small class="text-muted">{$marker->lat|default:'-'} / {$marker->lng|default:'-'}</small></td>
                                    <td><small class="text-muted">{$marker->pos_x|default:'-'}% / {$marker->pos_y|default:'-'}%</small></td>
                                    <td><a href="{$postURL}&bbf_page=areas&action=delete_marker&marker_id={$marker->id}&map_id={$map->id}" class="btn btn-sm btn-outline-danger" onclick="return confirm('Marker löschen?')"><i class="fa fa-trash"></i></a></td>
                                </tr>
                            {/foreach}
                        </tbody>
                    </table>
                </div>
            {/if}

            <div class="card">
                <div class="card-header"><strong>Neuen Marker hinzufügen</strong></div>
                <div class="card-body">
                    <form method="post" action="{$postURL}&bbf_page=areas&action=save_marker">
                        {$jtl_token}
                        <input type="hidden" name="map_id" value="{$map->id}">
                        <input type="hidden" name="marker_id" value="0">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Titel (DE) *</label>
                                <input type="text" name="marker_trans_ger_title" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Beschreibung (DE)</label>
                                <textarea name="marker_trans_ger_description" class="form-control" rows="1"></textarea>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Gruppe</label>
                                <select name="group_id" class="form-select">
                                    <option value="">Keine</option>
                                    {foreach $groups as $g}
                                        <option value="{$g->id}">{$g->name|default:$g->id}</option>
                                    {/foreach}
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Latitude</label>
                                <input type="number" step="0.0000001" name="lat" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Longitude</label>
                                <input type="number" step="0.0000001" name="lng" class="form-control">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Pos X (%)</label>
                                <input type="number" step="0.01" name="pos_x" class="form-control" min="0" max="100">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Pos Y (%)</label>
                                <input type="number" step="0.01" name="pos_y" class="form-control" min="0" max="100">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Sort</label>
                                <input type="number" name="marker_sort_order" value="0" class="form-control" min="0">
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-outline-primary"><i class="fa fa-plus"></i> Marker hinzufügen</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    {/if}
</div>
