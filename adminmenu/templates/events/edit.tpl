<div class="bbf-admin">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>{if $isEdit}Veranstaltung bearbeiten{else}Neue Veranstaltung{/if}</h2>
        <a href="?action=list" class="btn btn-outline-secondary">
            <i class="fa fa-arrow-left"></i> Zurück zur Liste
        </a>
    </div>

    {if isset($smarty.get.msg)}
        <div class="alert alert-success alert-dismissible fade show">
            {if $smarty.get.msg === 'created'}Veranstaltung erstellt.
            {elseif $smarty.get.msg === 'updated'}Änderungen gespeichert.
            {elseif $smarty.get.msg === 'duplicated'}Veranstaltung dupliziert.
            {/if}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    {/if}

    <form method="post" action="?action=save" enctype="multipart/form-data">
        <input type="hidden" name="event_id" value="{$event->id}">

        {* Tab-Navigation *}
        <ul class="nav nav-tabs mb-4" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#tab-general">Allgemein</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tab-dates">Termine</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tab-categories">Kategorien</a>
            </li>
            {if $isEdit}
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#tab-program">Programm</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#tab-tickets">Tickets</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#tab-partners">Partner</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#tab-knowledge">Wissenswertes</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#tab-area">Area / Karte</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#tab-media">Medien</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#tab-links">Verlinkungen</a>
                </li>
            {/if}
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tab-seo">SEO</a>
            </li>
            {if $isEdit}
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#tab-layout">Seitenlayout</a>
                </li>
            {/if}
        </ul>

        <div class="tab-content">
            {* ── Tab: Allgemein ── *}
            <div class="tab-pane fade show active" id="tab-general">
                {include file="{$smarty.current_dir}/_form_general.tpl"}
            </div>

            {* ── Tab: Termine ── *}
            <div class="tab-pane fade" id="tab-dates">
                {include file="{$smarty.current_dir}/_form_dates.tpl"}
            </div>

            {* ── Tab: Kategorien ── *}
            <div class="tab-pane fade" id="tab-categories">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Kategorien zuordnen</h5>
                        {if !empty($categories)}
                            {foreach $categories as $cat}
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox"
                                           name="categories[]" value="{$cat->id}"
                                           id="cat_{$cat->id}"
                                           {if in_array($cat->id, $assignedCategoryIds)} checked{/if}>
                                    <label class="form-check-label" for="cat_{$cat->id}">
                                        {$cat->getName()|default:$cat->slug}
                                        {if !$cat->isActive} <span class="badge bg-secondary">inaktiv</span>{/if}
                                    </label>
                                </div>
                            {/foreach}
                        {else}
                            <p class="text-muted">Keine Kategorien vorhanden. <a href="../categories?action=create">Kategorie erstellen</a></p>
                        {/if}
                    </div>
                </div>
            </div>

            {* ── Tab: Programm ── *}
            {if $isEdit}
                <div class="tab-pane fade" id="tab-program">
                    {include file="{$smarty.current_dir}/_form_program.tpl"}
                </div>
            {/if}

            {* ── Tab: Tickets ── *}
            {if $isEdit}
                <div class="tab-pane fade" id="tab-tickets">
                    {include file="{$smarty.current_dir}/_form_tickets.tpl"}
                </div>
            {/if}

            {* ── Tab: Partner ── *}
            {if $isEdit}
                <div class="tab-pane fade" id="tab-partners">
                    {include file="{$smarty.current_dir}/_form_partners.tpl"}
                </div>
            {/if}

            {* ── Tab: Wissenswertes ── *}
            {if $isEdit}
                <div class="tab-pane fade" id="tab-knowledge">
                    {include file="{$smarty.current_dir}/_form_knowledge.tpl"}
                </div>
            {/if}

            {* ── Tab: Area / Karte ── *}
            {if $isEdit}
                <div class="tab-pane fade" id="tab-area">
                    {include file="{$smarty.current_dir}/_form_area.tpl"}
                </div>
            {/if}

            {* ── Tab: Medien ── *}
            {if $isEdit}
                <div class="tab-pane fade" id="tab-media">
                    {include file="{$smarty.current_dir}/_form_media.tpl"}
                </div>
            {/if}

            {* ── Tab: Verlinkungen ── *}
            {if $isEdit}
                <div class="tab-pane fade" id="tab-links">
                    {include file="{$smarty.current_dir}/_form_links.tpl"}
                </div>
            {/if}

            {* ── Tab: SEO ── *}
            <div class="tab-pane fade" id="tab-seo">
                {include file="{$smarty.current_dir}/_form_seo.tpl"}
            </div>

            {* ── Tab: Layout ── *}
            {if $isEdit}
                <div class="tab-pane fade" id="tab-layout">
                    {include file="{$smarty.current_dir}/_form_layout.tpl"}
                </div>
            {/if}
        </div>

        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="fa fa-save"></i> {if $isEdit}Speichern{else}Erstellen{/if}
            </button>
            <a href="?action=list" class="btn btn-outline-secondary">Abbrechen</a>
        </div>
    </form>
</div>
