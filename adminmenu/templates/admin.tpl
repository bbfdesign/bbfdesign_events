<style>
    .bbf-admin-nav { display: flex; gap: 0.25rem; margin-bottom: 1.5rem; flex-wrap: wrap; }
    .bbf-admin-nav a { padding: 0.5rem 1rem; border-radius: 0.375rem; text-decoration: none; color: #4B5563; font-size: 0.875rem; font-weight: 500; transition: all 0.15s; }
    .bbf-admin-nav a:hover { background: #F3F4F6; color: #111827; }
    .bbf-admin-nav a.active { background: #2563EB; color: #fff; }
    .bbf-admin-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
    .bbf-admin-header h1 { font-size: 1.5rem; font-weight: 700; margin: 0; }
    .bbf-admin-version { font-size: 0.75rem; color: #9CA3AF; }
</style>

<div class="bbf-events-admin">
    <div class="bbf-admin-header">
        <h1>BBF Events</h1>
        <span class="bbf-admin-version">v{$pluginVersion}</span>
    </div>

    <div class="bbf-admin-nav">
        <a href="{$postURL}&bbf_page=events" class="{if $activePage === 'events' || $activePage === 'event_edit'}active{/if}">
            <i class="fa fa-calendar-alt"></i> Veranstaltungen
        </a>
        <a href="{$postURL}&bbf_page=categories" class="{if $activePage === 'categories' || $activePage === 'category_edit'}active{/if}">
            <i class="fa fa-tags"></i> Kategorien
        </a>
        <a href="{$postURL}&bbf_page=partners" class="{if $activePage === 'partners' || $activePage === 'partner_edit'}active{/if}">
            <i class="fa fa-handshake"></i> Partner
        </a>
        <a href="{$postURL}&bbf_page=knowledge" class="{if $activePage === 'knowledge' || $activePage === 'knowledge_edit'}active{/if}">
            <i class="fa fa-lightbulb"></i> Wissenswertes
        </a>
        <a href="{$postURL}&bbf_page=tickets" class="{if $activePage === 'tickets' || $activePage === 'ticket_edit'}active{/if}">
            <i class="fa fa-ticket-alt"></i> Tickets
        </a>
        <a href="{$postURL}&bbf_page=areas" class="{if $activePage === 'areas' || $activePage === 'area_edit'}active{/if}">
            <i class="fa fa-map-marked-alt"></i> Areas
        </a>
        <a href="{$postURL}&bbf_page=settings" class="{if $activePage === 'settings'}active{/if}">
            <i class="fa fa-cog"></i> Einstellungen
        </a>
    </div>

    {if isset($error)}
        <div class="alert alert-danger">{$error|escape:'html'}</div>
    {/if}

    <div class="bbf-admin-content">
        {* Controllers set activePage to specific values like 'event_edit' for form views *}
        {if $activePage === 'event_edit'}
            {include file="{$tplPath}events/edit.tpl"}
        {elseif $activePage === 'events'}
            {include file="{$tplPath}events/list.tpl"}
        {elseif $activePage === 'category_edit'}
            {include file="{$tplPath}categories/edit.tpl"}
        {elseif $activePage === 'categories'}
            {include file="{$tplPath}categories/list.tpl"}
        {elseif $activePage === 'partner_edit'}
            {include file="{$tplPath}partners/edit.tpl"}
        {elseif $activePage === 'partners'}
            {include file="{$tplPath}partners/list.tpl"}
        {elseif $activePage === 'knowledge_edit'}
            {include file="{$tplPath}knowledge/edit.tpl"}
        {elseif $activePage === 'knowledge'}
            {include file="{$tplPath}knowledge/list.tpl"}
        {elseif $activePage === 'ticket_edit'}
            {include file="{$tplPath}tickets/edit.tpl"}
        {elseif $activePage === 'tickets'}
            {include file="{$tplPath}tickets/list.tpl"}
        {elseif $activePage === 'area_edit'}
            {include file="{$tplPath}areas/edit.tpl"}
        {elseif $activePage === 'areas'}
            {include file="{$tplPath}areas/list.tpl"}
        {elseif $activePage === 'settings'}
            {include file="{$tplPath}settings/index.tpl"}
        {else}
            {include file="{$tplPath}events/list.tpl"}
        {/if}
    </div>
</div>
