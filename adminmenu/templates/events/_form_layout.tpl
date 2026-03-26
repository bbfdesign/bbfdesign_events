<div class="alert alert-info mb-3">
    <strong>Hinweis:</strong> Wenn kein Pagebuilder-Layout vorhanden ist, wird die Detailseite
    automatisch aus den Stammdaten (Hero, Beschreibung, Sidebar) generiert.
</div>

{* GrapesJS Pagebuilder – inline im Tab, analog zum BBF Formbuilder *}
{include file="{$tplPath}events/pagebuilder.tpl"}

{* GrapesJS CSS (nur laden wenn Tab aktiv) *}
<link rel="stylesheet" href="https://unpkg.com/grapesjs@0.21.13/dist/css/grapes.min.css">

{* GrapesJS JS + BBF Pagebuilder Bundle *}
<script src="https://unpkg.com/grapesjs@0.21.13/dist/grapes.min.js"></script>
<script src="https://unpkg.com/grapesjs-preset-webpage@1.0.3/dist/index.js"></script>

{* BBF Pagebuilder IIFE – falls npm build durchgeführt, aus dist laden *}
{* Fallback: CDN wird im pagebuilder.tpl direkt init *}
{if file_exists("{$tplPath}../../js/dist/bbf-pagebuilder.iife.js")}
    <script src="{$adminUrl}/../plugins/bbfdesign_events/adminmenu/js/dist/bbf-pagebuilder.iife.js"></script>
{/if}
