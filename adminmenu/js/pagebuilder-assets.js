/**
 * BBF Events – GrapesJS Asset Manager Integration
 * Handles image upload to /mediafiles/bbfdesign_events/
 */

export function setupAssetManager(editor, csrfToken) {
    // Load existing media files
    fetch('/admin/plugin/bbfdesign_events/api/media/list')
        .then((r) => r.json())
        .then((assets) => {
            editor.AssetManager.add(
                assets.map((a) => ({
                    type: 'image',
                    src: a.url,
                    name: a.filename,
                }))
            );
        })
        .catch(() => {
            // Silent fail if API not yet available
        });

    // Handle upload response
    editor.on('asset:upload:response', (response) => {
        const data = typeof response === 'string' ? JSON.parse(response) : response;
        if (data.success && data.files) {
            data.files.forEach((file) => {
                editor.AssetManager.add({
                    type: 'image',
                    src: file.url,
                    name: file.filename,
                });
            });
        }
    });

    // Drag & drop images onto canvas
    editor.on('canvas:drop', (dataTransfer) => {
        const files = dataTransfer?.files;
        if (!files?.length) return;

        const imageFiles = Array.from(files).filter((f) => f.type.startsWith('image/'));
        if (!imageFiles.length) return;

        uploadFiles(imageFiles, csrfToken).then((urls) => {
            urls.forEach((url) => {
                editor.addComponents({
                    tagName: 'img',
                    attributes: { src: url, alt: '', loading: 'lazy' },
                    classes: ['img-fluid'],
                });
            });
        });
    });
}

async function uploadFiles(files, csrfToken) {
    const formData = new FormData();
    files.forEach((f) => formData.append('files[]', f));
    formData.append('context', 'images');

    try {
        const response = await fetch('/admin/plugin/bbfdesign_events/api/media/upload', {
            method: 'POST',
            headers: { 'X-CSRF-Token': csrfToken },
            body: formData,
        });
        const data = await response.json();
        return data.files?.map((f) => f.url) || [];
    } catch {
        return [];
    }
}
