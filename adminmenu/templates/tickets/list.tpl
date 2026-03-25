<div class="bbf-admin">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Ticket-Verwaltung</h2>
        <a href="?action=create_category" class="btn btn-primary"><i class="fa fa-plus"></i> Neue Ticket-Kategorie</a>
    </div>

    {if isset($smarty.get.msg)}
        <div class="alert alert-success alert-dismissible fade show">
            {if $smarty.get.msg === 'deleted'}Gelöscht.{elseif $smarty.get.msg === 'created'}Erstellt.{else}Aktualisiert.{/if}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    {/if}

    {* Ticket-Kategorien *}
    <h4 class="mb-3">Ticket-Kategorien</h4>
    <div class="card mb-5">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th width="50">ID</th>
                        <th width="20"></th>
                        <th>Name</th>
                        <th>Slug</th>
                        <th width="80">Tickets</th>
                        <th width="80">Sort</th>
                        <th width="150">Aktionen</th>
                    </tr>
                </thead>
                <tbody>
                    {if !empty($categories)}
                        {foreach $categories as $cat}
                            <tr>
                                <td class="text-muted">{$cat->id}</td>
                                <td><span class="d-inline-block rounded-circle" style="width:14px;height:14px;background:{$cat->color};"></span></td>
                                <td><a href="?action=edit_category&id={$cat->id}" class="fw-bold text-decoration-none">{$cat->name|default:'(kein Name)'}</a></td>
                                <td><code>{$cat->slug}</code></td>
                                <td>{$cat->ticket_count}</td>
                                <td>{$cat->sort_order}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="?action=edit_category&id={$cat->id}" class="btn btn-outline-primary"><i class="fa fa-edit"></i></a>
                                        <a href="?action=delete_category&id={$cat->id}" class="btn btn-outline-danger" onclick="return confirm('Kategorie wirklich löschen?')"><i class="fa fa-trash"></i></a>
                                    </div>
                                </td>
                            </tr>
                        {/foreach}
                    {else}
                        <tr><td colspan="7" class="text-center py-4 text-muted">Keine Ticket-Kategorien vorhanden.</td></tr>
                    {/if}
                </tbody>
            </table>
        </div>
    </div>

    {* Tickets-Übersicht *}
    <h4 class="mb-3">Tickets nach Events</h4>
    <div class="alert alert-info">
        <i class="fa fa-info-circle"></i> Tickets werden direkt in der Event-Bearbeitungsmaske verwaltet (Tab "Tickets").
    </div>
    {if !empty($tickets)}
        <div class="card">
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr><th>Event</th><th>Ticket</th><th>Typ</th><th>Kategorie</th><th>Aktiv</th><th>Ausverkauft</th></tr>
                    </thead>
                    <tbody>
                        {foreach $tickets as $t}
                            <tr>
                                <td><a href="../events?action=edit&id={$t->event_id}">{$t->event_title|default:$t->event_slug}</a></td>
                                <td>{$t->ticket_name|default:'(kein Name)'}</td>
                                <td><small class="badge bg-light text-dark">{$t->source_type}</small></td>
                                <td>{$t->cat_name|default:'-'}</td>
                                <td>{if $t->is_active}<span class="text-success">Ja</span>{else}<span class="text-muted">Nein</span>{/if}</td>
                                <td>{if $t->is_sold_out}<span class="badge bg-danger">Ausverkauft</span>{/if}</td>
                            </tr>
                        {/foreach}
                    </tbody>
                </table>
            </div>
        </div>
    {/if}
</div>
