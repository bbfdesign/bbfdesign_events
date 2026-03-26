<div class="bbf-admin">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Partner / Sponsoren</h2>
        <a href="{$postURL}&bbf_page=partners&action=create" class="btn btn-primary"><i class="fa fa-plus"></i> Neuer Partner</a>
    </div>

    {if isset($smarty.get.msg)}
        <div class="alert alert-success alert-dismissible fade show">
            Partner {if $smarty.get.msg === 'deleted'}gelöscht{elseif $smarty.get.msg === 'created'}erstellt{else}aktualisiert{/if}.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    {/if}

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th width="50">ID</th>
                        <th width="60">Logo</th>
                        <th>Name</th>
                        <th>Website</th>
                        <th width="80">Aktiv</th>
                        <th width="80">Sort</th>
                        <th width="150">Aktionen</th>
                    </tr>
                </thead>
                <tbody>
                    {if !empty($partners)}
                        {foreach $partners as $p}
                            <tr>
                                <td class="text-muted">{$p->id}</td>
                                <td>{if $p->logo}<img src="{$p->logo}" alt="" style="max-height:30px; max-width:50px;">{/if}</td>
                                <td><a href="{$postURL}&bbf_page=partners&action=edit&id={$p->id}" class="fw-bold text-decoration-none">{$p->name|default:'(kein Name)'}</a></td>
                                <td><small class="text-muted">{$p->website_url|default:'-'|truncate:40}</small></td>
                                <td>{if $p->is_active}<span class="badge bg-success">Ja</span>{else}<span class="badge bg-secondary">Nein</span>{/if}</td>
                                <td>{$p->sort_order}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{$postURL}&bbf_page=partners&action=edit&id={$p->id}" class="btn btn-outline-primary"><i class="fa fa-edit"></i></a>
                                        <a href="{$postURL}&bbf_page=partners&action=delete&id={$p->id}" class="btn btn-outline-danger" onclick="return confirm('Partner wirklich löschen?')"><i class="fa fa-trash"></i></a>
                                    </div>
                                </td>
                            </tr>
                        {/foreach}
                    {else}
                        <tr><td colspan="7" class="text-center py-4 text-muted">Keine Partner vorhanden.</td></tr>
                    {/if}
                </tbody>
            </table>
        </div>
    </div>
</div>
