<div class="bbf-admin">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Veranstaltungen</h2>
        <a href="?action=create" class="btn btn-primary">
            <i class="fa fa-plus"></i> Neue Veranstaltung
        </a>
    </div>

    {if isset($smarty.get.msg)}
        <div class="alert alert-success alert-dismissible fade show">
            {if $smarty.get.msg === 'deleted'}Veranstaltung gelöscht.
            {elseif $smarty.get.msg === 'created'}Veranstaltung erstellt.
            {elseif $smarty.get.msg === 'updated'}Veranstaltung aktualisiert.
            {/if}
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
                        <th width="120">Status</th>
                        <th width="120">Typ</th>
                        <th width="100">Featured</th>
                        <th width="140">Erstellt</th>
                        <th width="180">Aktionen</th>
                    </tr>
                </thead>
                <tbody>
                    {if !empty($events)}
                        {foreach $events as $event}
                            <tr>
                                <td class="text-muted">{$event->id}</td>
                                <td>
                                    <a href="?action=edit&id={$event->id}" class="fw-bold text-decoration-none">
                                        {$event->getTitle()|default:'(kein Titel)'|escape:'html'}
                                    </a>
                                    <br>
                                    <small class="text-muted">/{$event->slug}</small>
                                </td>
                                <td>
                                    <span class="badge {$event->status->badgeClass()}">
                                        {$event->status->label()}
                                    </span>
                                </td>
                                <td>
                                    <small>{$event->eventType->label()}</small>
                                </td>
                                <td>
                                    {if $event->isFeatured}
                                        <span class="badge bg-warning text-dark">Featured</span>
                                    {/if}
                                </td>
                                <td>
                                    <small>{$event->createdAt->format('d.m.Y H:i')}</small>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="?action=edit&id={$event->id}" class="btn btn-outline-primary" title="Bearbeiten">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        <a href="?action=duplicate&id={$event->id}" class="btn btn-outline-secondary" title="Duplizieren">
                                            <i class="fa fa-copy"></i>
                                        </a>
                                        <a href="?action=delete&id={$event->id}"
                                           class="btn btn-outline-danger"
                                           title="Löschen"
                                           onclick="return confirm('Veranstaltung wirklich löschen?')">
                                            <i class="fa fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        {/foreach}
                    {else}
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                Noch keine Veranstaltungen vorhanden.
                                <br>
                                <a href="?action=create" class="btn btn-sm btn-primary mt-2">Erste Veranstaltung erstellen</a>
                            </td>
                        </tr>
                    {/if}
                </tbody>
            </table>
        </div>
    </div>

    {if $pagination->totalPages > 1}
        <nav class="mt-3">
            <ul class="pagination justify-content-center">
                {for $i=1 to $pagination->totalPages}
                    <li class="page-item{if $i === $pagination->page} active{/if}">
                        <a class="page-link" href="?page={$i}">{$i}</a>
                    </li>
                {/for}
            </ul>
        </nav>
    {/if}
</div>
