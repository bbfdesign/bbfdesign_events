<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <strong>Visueller Seiteneditor (GrapesJS)</strong>
        <a href="{$postURL}&bbf_page=events&action=pagebuilder&id={$event->id}" class="btn btn-sm btn-primary" target="_blank">
            <i class="fa fa-external-link-alt"></i> Pagebuilder öffnen
        </a>
    </div>
    <div class="card-body">
        <p class="text-muted">
            Der visuelle Pagebuilder ermöglicht es, die Detailseite per Drag-and-Drop zu gestalten.
            Öffnen Sie den Pagebuilder in einem separaten Fenster, um das Layout zu bearbeiten.
        </p>

        <div class="alert alert-info">
            <strong>Hinweis:</strong> Wenn kein Pagebuilder-Layout vorhanden ist, wird die Detailseite
            automatisch aus den Stammdaten (Hero, Beschreibung, Sidebar) generiert.
        </div>

        <div id="bbf-pagebuilder-preview" style="min-height: 200px; border: 2px dashed #dee2e6; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center;">
            <div class="text-center text-muted">
                <i class="fa fa-paint-brush fa-3x mb-3 d-block"></i>
                <p>Pagebuilder-Vorschau</p>
                <a href="{$postURL}&bbf_page=events&action=pagebuilder&id={$event->id}" class="btn btn-outline-primary" target="_blank">
                    Seitenlayout bearbeiten
                </a>
            </div>
        </div>
    </div>
</div>
