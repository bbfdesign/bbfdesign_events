<div class="bbf-admin">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Areas / Karten</h2>
        <a href="?action=create" class="btn btn-primary"><i class="fa fa-plus"></i> Neue Karte</a>
    </div>

    {if isset($smarty.get.msg)}
        <div class="alert alert-success alert-dismissible fade show">
            {if $smarty.get.msg === 'deleted'}Karte gelöscht.{elseif $smarty.get.msg === 'created'}Karte erstellt.{else}Karte aktualisiert.{/if}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    {/if}

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th width="50">ID</th>
                        <th>Titel</th>
                        <th>Typ</th>
                        <th width="80">Marker</th>
                        <th width="80">Events</th>
                        <th width="80">Aktiv</th>
                        <th width="150">Aktionen</th>
                    </tr>
                </thead>
                <tbody>
                    {if !empty($maps)}
                        {foreach $maps as $map}
                            <tr>
                                <td class="text-muted">{$map->id}</td>
                                <td><a href="?action=edit&id={$map->id}" class="fw-bold text-decoration-none">{$map->title|default:'(kein Titel)'}</a></td>
                                <td><span class="badge bg-light text-dark">{$map->map_type}</span></td>
                                <td>{$map->marker_count}</td>
                                <td>{$map->event_count}</td>
                                <td>{if $map->is_active}<span class="badge bg-success">Ja</span>{else}<span class="badge bg-secondary">Nein</span>{/if}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="?action=edit&id={$map->id}" class="btn btn-outline-primary"><i class="fa fa-edit"></i></a>
                                        <a href="?action=delete&id={$map->id}" class="btn btn-outline-danger" onclick="return confirm('Karte wirklich löschen?')"><i class="fa fa-trash"></i></a>
                                    </div>
                                </td>
                            </tr>
                        {/foreach}
                    {else}
                        <tr><td colspan="7" class="text-center py-4 text-muted">Keine Karten vorhanden.</td></tr>
                    {/if}
                </tbody>
            </table>
        </div>
    </div>
</div>
