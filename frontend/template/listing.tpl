{**
 * Wrapper für JTL FrontendLink.
 * JTL sucht das Template in frontend/template/ – die echte Datei liegt in events/.
 * Daten ($events, $pagination, $filter etc.) werden vom SeoHook via HOOK_SMARTY_INC injiziert.
 *}
{if isset($bbfEventsPath)}
    {include file="{$bbfEventsPath}listing.tpl"}
{else}
    {include file=$oPlugin->cFrontendPfad|cat:'template/events/listing.tpl'}
{/if}
