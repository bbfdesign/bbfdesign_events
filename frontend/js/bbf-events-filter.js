/**
 * BBF Events – Filter Logic (lazy loaded on listing page)
 */
(function () {
    'use strict';

    const filterBar = document.querySelector('.bbf-filter-bar');
    if (!filterBar) return;

    // Date range filter enhancement
    const dateFrom = filterBar.querySelector('[name="date_from"]');
    const dateTo = filterBar.querySelector('[name="date_to"]');

    if (dateFrom && dateTo) {
        dateFrom.addEventListener('change', function () {
            if (dateTo.value && dateTo.value < dateFrom.value) {
                dateTo.value = dateFrom.value;
            }
            dateTo.min = dateFrom.value;
        });
    }

    // Search debounce for live search (if AJAX filter is added later)
    const searchInput = filterBar.querySelector('[name="q"]');
    if (searchInput) {
        let debounceTimer;
        searchInput.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function () {
                // Placeholder for future AJAX filtering
            }, 300);
        });
    }

})();
