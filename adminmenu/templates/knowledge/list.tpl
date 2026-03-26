<div class="bbf-admin">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Wissenswertes</h2>
        <a href="{$postURL}&bbf_page=knowledge&action=create" class="btn btn-primary"><i class="fa fa-plus"></i> Neuer Eintrag</a>
    </div>

    {if isset($smarty.get.msg)}
        <div class="alert alert-success alert-dismissible fade show">
            Eintrag {if $smarty.get.msg === 'deleted'}gelöscht{elseif $smarty.get.msg === 'created'}erstellt{else}aktualisiert{/if}.
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
                        <th>Slug</th>
                        <th width="80">Aktiv</th>
                        <th width="80">Sort</th>
                        <th width="150">Aktionen</th>
                    </tr>
                </thead>
                <tbody>
                    {if !empty($items)}
                        {foreach $items as $item}
                            <tr>
                                <td class="text-muted">{$item->id}</td>
                                <td><a href="{$postURL}&bbf_page=knowledge&action=edit&id={$item->id}" class="fw-bold text-decoration-none">{$item->title|default:'(kein Titel)'}</a></td>
                                <td><code>{$item->slug}</code></td>
                                <td>{if $item->is_active}<span class="badge bg-success">Ja</span>{else}<span class="badge bg-secondary">Nein</span>{/if}</td>
                                <td>{$item->sort_order}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{$postURL}&bbf_page=knowledge&action=edit&id={$item->id}" class="btn btn-outline-primary"><i class="fa fa-edit"></i></a>
                                        <a href="{$postURL}&bbf_page=knowledge&action=delete&id={$item->id}" class="btn btn-outline-danger" onclick="return confirm('Eintrag wirklich löschen?')"><i class="fa fa-trash"></i></a>
                                    </div>
                                </td>
                            </tr>
                        {/foreach}
                    {else}
                        <tr><td colspan="6" class="text-center py-4 text-muted">Keine Einträge vorhanden.</td></tr>
                    {/if}
                </tbody>
            </table>
        </div>
    </div>
</div>
