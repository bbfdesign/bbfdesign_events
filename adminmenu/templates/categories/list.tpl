<div class="bbf-admin">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Event-Kategorien</h2>
        <a href="?action=create" class="btn btn-primary"><i class="fa fa-plus"></i> Neue Kategorie</a>
    </div>

    {if isset($smarty.get.msg)}
        <div class="alert alert-success alert-dismissible fade show">
            {if $smarty.get.msg === 'deleted'}Kategorie gelöscht.{elseif $smarty.get.msg === 'created'}Kategorie erstellt.{elseif $smarty.get.msg === 'updated'}Kategorie aktualisiert.{/if}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    {/if}

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th width="50">ID</th>
                        <th>Name</th>
                        <th>Slug</th>
                        <th width="80">Aktiv</th>
                        <th width="80">Events</th>
                        <th width="80">Sort</th>
                        <th width="150">Aktionen</th>
                    </tr>
                </thead>
                <tbody>
                    {if !empty($categories)}
                        {foreach $categories as $cat}
                            <tr>
                                <td class="text-muted">{$cat->id}</td>
                                <td><a href="?action=edit&id={$cat->id}" class="fw-bold text-decoration-none">{$cat->getName()|default:'(kein Name)'}</a></td>
                                <td><code>{$cat->slug}</code></td>
                                <td>{if $cat->isActive}<span class="badge bg-success">Ja</span>{else}<span class="badge bg-secondary">Nein</span>{/if}</td>
                                <td>{$repository->getEventCount($cat->id)}</td>
                                <td>{$cat->sortOrder}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="?action=edit&id={$cat->id}" class="btn btn-outline-primary"><i class="fa fa-edit"></i></a>
                                        <a href="?action=delete&id={$cat->id}" class="btn btn-outline-danger" onclick="return confirm('Kategorie wirklich löschen?')"><i class="fa fa-trash"></i></a>
                                    </div>
                                </td>
                            </tr>
                        {/foreach}
                    {else}
                        <tr><td colspan="7" class="text-center py-4 text-muted">Keine Kategorien vorhanden.</td></tr>
                    {/if}
                </tbody>
            </table>
        </div>
    </div>
</div>
