<div class="bbf-admin">
    <h2 class="mb-4">BBF Events – Einstellungen</h2>

    {if isset($msg)}
        <div class="alert alert-success alert-dismissible fade show">
            {$msg}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    {/if}

    <div class="row">
        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header"><strong>Plugin-Konfiguration</strong></div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr><td class="text-muted">Basis-Pfad</td><td><code>/{$config.base_path}</code></td></tr>
                        <tr><td class="text-muted">Items pro Seite</td><td>{$config.items_per_page}</td></tr>
                        <tr><td class="text-muted">Cache TTL Listing</td><td>{$config.cache_ttl_listing}s</td></tr>
                        <tr><td class="text-muted">Cache TTL Detail</td><td>{$config.cache_ttl_detail}s</td></tr>
                        <tr><td class="text-muted">Medien-Verzeichnis</td><td><code>{$config.media_base_dir}</code></td></tr>
                        <tr><td class="text-muted">Max. Upload-Größe</td><td>{$config.max_upload_size}</td></tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header"><strong>Wartung</strong></div>
                <div class="card-body">
                    <form method="post">
                        {$jtl_token}
                        <input type="hidden" name="action" value="clear_cache">
                        <p class="text-muted">Leert alle Event-bezogenen Caches.</p>
                        <button type="submit" class="btn btn-warning">
                            <i class="fa fa-sync"></i> Cache leeren
                        </button>
                    </form>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header"><strong>URL-Struktur</strong></div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr><td class="text-muted">Listing</td><td><code>/veranstaltungen</code></td></tr>
                        <tr><td class="text-muted">Kategorie</td><td><code>/veranstaltungen/kategorie/{ldelim}slug{rdelim}</code></td></tr>
                        <tr><td class="text-muted">Detail</td><td><code>/veranstaltungen/{ldelim}slug{rdelim}</code></td></tr>
                        <tr><td class="text-muted">Archiv</td><td><code>/veranstaltungen/archiv</code></td></tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
