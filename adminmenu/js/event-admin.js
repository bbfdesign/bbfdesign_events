/**
 * BBF Events – Event Admin JS
 * Handles dynamic form interactions in the event edit view.
 */
(function () {
    'use strict';

    // ── Tab persistence via URL hash ──────────────────────
    const tabLinks = document.querySelectorAll('[data-bs-toggle="tab"]');
    if (window.location.hash) {
        const activeTab = document.querySelector(`[href="${window.location.hash}"]`);
        if (activeTab) {
            const bsTab = new bootstrap.Tab(activeTab);
            bsTab.show();
        }
    }
    tabLinks.forEach(function (link) {
        link.addEventListener('shown.bs.tab', function (e) {
            history.replaceState(null, null, e.target.getAttribute('href'));
        });
    });

    // ── Slug auto-generation ──────────────────────────────
    const slugField = document.querySelector('input[name="slug"]');
    const titleField = document.querySelector('input[name="trans_ger_title"]');
    if (slugField && titleField && slugField.value === '') {
        titleField.addEventListener('blur', function () {
            if (slugField.value === '') {
                slugField.value = generateSlug(titleField.value);
            }
        });
    }

    function generateSlug(text) {
        const map = { 'ä': 'ae', 'ö': 'oe', 'ü': 'ue', 'ß': 'ss' };
        return text
            .toLowerCase()
            .replace(/[äöüß]/g, function (m) { return map[m] || m; })
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/[\s-]+/g, '-')
            .replace(/^-+|-+$/g, '');
    }

    // ── Hero image preview ────────────────────────────────
    const heroInput = document.querySelector('input[name="hero_image"]');
    if (heroInput) {
        heroInput.addEventListener('change', function () {
            const preview = this.parentNode.querySelector('img');
            if (preview) {
                preview.src = this.value;
            }
        });
    }

    // ── Confirm delete ────────────────────────────────────
    document.querySelectorAll('[data-confirm]').forEach(function (el) {
        el.addEventListener('click', function (e) {
            if (!confirm(this.dataset.confirm)) {
                e.preventDefault();
            }
        });
    });

    // ── Auto-dismiss alerts ───────────────────────────────
    document.querySelectorAll('.alert-dismissible').forEach(function (alert) {
        setTimeout(function () {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            bsAlert.close();
        }, 5000);
    });

})();
