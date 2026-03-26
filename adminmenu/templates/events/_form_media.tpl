<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <strong>Medien</strong>
        <button type="button" class="btn btn-sm btn-outline-primary" id="bbf-add-media">
            <i class="fa fa-plus"></i> Medium hinzufügen
        </button>
    </div>
    <div class="card-body">
        <div id="bbf-media-container">
            {if !empty($eventMedia)}
                {foreach $eventMedia as $idx => $media}
                    <div class="bbf-media-entry border rounded p-3 mb-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge bg-light text-dark">{$media->media_type}</span>
                            <button type="button" class="btn btn-sm btn-outline-danger bbf-remove-media"><i class="fa fa-times"></i></button>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Typ</label>
                                <select name="media[{$idx}][media_type]" class="form-select form-select-sm">
                                    <option value="image"{if $media->media_type === 'image'} selected{/if}>Bild</option>
                                    <option value="gallery"{if $media->media_type === 'gallery'} selected{/if}>Galerie</option>
                                    <option value="youtube"{if $media->media_type === 'youtube'} selected{/if}>YouTube</option>
                                    <option value="vimeo"{if $media->media_type === 'vimeo'} selected{/if}>Vimeo</option>
                                    <option value="local_video"{if $media->media_type === 'local_video'} selected{/if}>Lokales Video</option>
                                    <option value="download"{if $media->media_type === 'download'} selected{/if}>Download</option>
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label">Datei / URL</label>
                                <input type="text" name="media[{$idx}][file_path]" value="{$media->file_path|default:$media->external_url|escape:'html'}" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Alt-Text / Titel</label>
                                <input type="text" name="media[{$idx}][alt_text]" value="{$media->alt_text|default:''|escape:'html'}" class="form-control form-control-sm">
                            </div>
                        </div>
                        <input type="hidden" name="media[{$idx}][id]" value="{$media->id}">
                    </div>
                {/foreach}
            {else}
                <p class="text-muted text-center py-3" id="bbf-no-media">Noch keine Medien.</p>
            {/if}
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var container = document.getElementById('bbf-media-container');
    var addBtn = document.getElementById('bbf-add-media');
    var idx = container.querySelectorAll('.bbf-media-entry').length;

    addBtn.addEventListener('click', function() {
        var noMsg = document.getElementById('bbf-no-media');
        if (noMsg) noMsg.style.display = 'none';
        var i = idx++;
        container.insertAdjacentHTML('beforeend',
            '<div class="bbf-media-entry border rounded p-3 mb-3">' +
            '<div class="d-flex justify-content-between align-items-start mb-2"><span class="badge bg-light text-dark">Neues Medium</span><button type="button" class="btn btn-sm btn-outline-danger bbf-remove-media"><i class="fa fa-times"></i></button></div>' +
            '<div class="row g-3">' +
            '<div class="col-md-3"><label class="form-label">Typ</label><select name="media['+i+'][media_type]" class="form-select form-select-sm"><option value="image">Bild</option><option value="gallery">Galerie</option><option value="youtube">YouTube</option><option value="vimeo">Vimeo</option><option value="local_video">Lokales Video</option><option value="download">Download</option></select></div>' +
            '<div class="col-md-5"><label class="form-label">Datei / URL</label><input type="text" name="media['+i+'][file_path]" class="form-control form-control-sm" placeholder="/mediafiles/bbfdesign_events/..."></div>' +
            '<div class="col-md-4"><label class="form-label">Alt-Text / Titel</label><input type="text" name="media['+i+'][alt_text]" class="form-control form-control-sm"></div>' +
            '</div></div>');
    });

    container.addEventListener('click', function(e) {
        if (e.target.closest('.bbf-remove-media')) e.target.closest('.bbf-media-entry').remove();
    });
});
</script>
