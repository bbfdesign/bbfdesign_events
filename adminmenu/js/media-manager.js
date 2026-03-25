/**
 * BBF Events – Media Manager
 * Handles image upload and selection in admin forms.
 */
(function () {
    'use strict';

    // ── File input enhancement ────────────────────────────
    document.querySelectorAll('.bbf-media-input').forEach(function (wrapper) {
        const input = wrapper.querySelector('input[type="text"]');
        const preview = wrapper.querySelector('.bbf-media-preview');
        const uploadBtn = wrapper.querySelector('.bbf-media-upload');
        const fileInput = wrapper.querySelector('input[type="file"]');
        const clearBtn = wrapper.querySelector('.bbf-media-clear');

        if (uploadBtn && fileInput) {
            uploadBtn.addEventListener('click', function () {
                fileInput.click();
            });

            fileInput.addEventListener('change', function () {
                if (this.files.length === 0) return;

                const formData = new FormData();
                formData.append('files[]', this.files[0]);
                formData.append('context', wrapper.dataset.context || 'images');

                fetch('/admin/plugin/bbfdesign_events/api/media/upload', {
                    method: 'POST',
                    headers: { 'X-CSRF-Token': window.BBF_CSRF_TOKEN || '' },
                    body: formData,
                })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.success && data.files && data.files[0]) {
                        const url = data.files[0].url;
                        if (input) input.value = url;
                        if (preview) {
                            preview.innerHTML = '<img src="' + url + '" alt="Preview" style="max-height:100px;border-radius:0.25rem;">';
                        }
                    }
                })
                .catch(function (err) {
                    console.error('Upload failed:', err);
                });

                this.value = '';
            });
        }

        if (clearBtn && input) {
            clearBtn.addEventListener('click', function () {
                input.value = '';
                if (preview) preview.innerHTML = '';
            });
        }

        // Live preview on URL change
        if (input && preview) {
            input.addEventListener('change', function () {
                if (this.value) {
                    preview.innerHTML = '<img src="' + this.value + '" alt="Preview" style="max-height:100px;border-radius:0.25rem;">';
                } else {
                    preview.innerHTML = '';
                }
            });
        }
    });

    // ── Drag & drop on any bbf-media-dropzone ─────────────
    document.querySelectorAll('.bbf-media-dropzone').forEach(function (zone) {
        zone.addEventListener('dragover', function (e) {
            e.preventDefault();
            zone.classList.add('bbf-media-dropzone--active');
        });

        zone.addEventListener('dragleave', function () {
            zone.classList.remove('bbf-media-dropzone--active');
        });

        zone.addEventListener('drop', function (e) {
            e.preventDefault();
            zone.classList.remove('bbf-media-dropzone--active');

            const files = e.dataTransfer.files;
            if (!files.length) return;

            const formData = new FormData();
            Array.from(files).forEach(function (f) { formData.append('files[]', f); });
            formData.append('context', zone.dataset.context || 'images');

            fetch('/admin/plugin/bbfdesign_events/api/media/upload', {
                method: 'POST',
                headers: { 'X-CSRF-Token': window.BBF_CSRF_TOKEN || '' },
                body: formData,
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success && data.files) {
                    const event = new CustomEvent('bbf:media:uploaded', { detail: data.files });
                    zone.dispatchEvent(event);
                }
            });
        });
    });

})();
