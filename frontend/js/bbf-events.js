/**
 * BBF Events – Core JS (minimal, loaded on all event pages)
 */
(function () {
    'use strict';

    // Auto-submit filter form on select change
    document.querySelectorAll('.bbf-filter-bar__select').forEach(function (select) {
        select.addEventListener('change', function () {
            this.closest('form').submit();
        });
    });

})();
