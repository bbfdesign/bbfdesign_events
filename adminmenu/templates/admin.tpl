<link rel="stylesheet" href="{$adminUrl}css/admin.css">

<div class="bbf-plugin-page">
    {$jtl_token}

    {* ═════ Sidebar Navigation ═════ *}
    <div class="bbf-sidebar" id="bbf-sidebar">
        <div class="bbf-sidebar-header">
            <div class="bbf-sidebar-logo">
                <img src="{$adminUrl}images/Logo_bbfdesign_dark_2024.png" alt="bbf.design" class="bbf-logo-img">
            </div>
        </div>

        <div class="bbf-sidebar-content">
            {* ── VERWALTUNG ── *}
            <div class="bbf-nav-section">VERWALTUNG</div>
            <ul class="bbf-sidebar-nav">
                <li>
                    <a href="{$postURL}&bbf_page=events" class="{if $activePage === 'events' || $activePage === 'event_edit'}active{/if}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                        <span>Veranstaltungen</span>
                    </a>
                </li>
                <li>
                    <a href="{$postURL}&bbf_page=categories" class="{if $activePage === 'categories' || $activePage === 'category_edit'}active{/if}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path><line x1="7" y1="7" x2="7.01" y2="7"></line></svg>
                        <span>Kategorien</span>
                    </a>
                </li>
                <li>
                    <a href="{$postURL}&bbf_page=partners" class="{if $activePage === 'partners' || $activePage === 'partner_edit'}active{/if}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                        <span>Partner</span>
                    </a>
                </li>
                <li>
                    <a href="{$postURL}&bbf_page=knowledge" class="{if $activePage === 'knowledge' || $activePage === 'knowledge_edit'}active{/if}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                        <span>Wissenswertes</span>
                    </a>
                </li>
                <li>
                    <a href="{$postURL}&bbf_page=tickets" class="{if $activePage === 'tickets' || $activePage === 'ticket_edit'}active{/if}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12h6a2 2 0 0 1 2 2v6a2 2 0 0 1-2 2H2V12z"></path><path d="M14 2h6a2 2 0 0 1 2 2v6a2 2 0 0 1-2 2h-6V2z"></path><rect x="14" y="14" width="10" height="8" rx="2"></rect><rect x="2" y="2" width="10" height="8" rx="2"></rect></svg>
                        <span>Tickets</span>
                    </a>
                </li>
                <li>
                    <a href="{$postURL}&bbf_page=areas" class="{if $activePage === 'areas' || $activePage === 'area_edit'}active{/if}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                        <span>Areas / Karten</span>
                    </a>
                </li>
            </ul>

            {* ── EINSTELLUNGEN ── *}
            <div class="bbf-nav-section">EINSTELLUNGEN</div>
            <ul class="bbf-sidebar-nav">
                <li>
                    <a href="{$postURL}&bbf_page=settings" class="{if $activePage === 'settings'}active{/if}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                        <span>Allgemein</span>
                    </a>
                </li>
            </ul>
        </div>

        <div class="bbf-sidebar-footer">
            <span class="bbf-version">v{$pluginVersion}</span>
        </div>
    </div>

    {* ═════ Main Content ═════ *}
    <div class="bbf-main">
        <div class="bbf-header">
            <div class="bbf-header-inner">
                <div>
                    <h3 class="bbf-header-title">BBF Events</h3>
                    <p class="bbf-header-subtitle">Event-Management für JTL-Shop 5</p>
                </div>
            </div>
        </div>

        <div class="bbf-content">
            {if isset($error)}
                <div class="alert alert-danger">{$error|escape:'html'}</div>
            {/if}

            {* Template-Routing based on activePage *}
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
</div>
