/**
 * BBF Events – Map Integration (lazy loaded)
 * Uses Leaflet (MIT) for interactive maps with marker groups.
 * Only loaded when an area_map block is present.
 */
(function () {
    'use strict';

    const mapContainers = document.querySelectorAll('.bbf-area-map__canvas[data-map-type="interactive"]');
    if (!mapContainers.length) return;

    // ── Load Leaflet dynamically ──────────────────────────
    function loadLeaflet() {
        return new Promise(function (resolve) {
            if (typeof L !== 'undefined') {
                resolve();
                return;
            }

            var css = document.createElement('link');
            css.rel = 'stylesheet';
            css.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
            document.head.appendChild(css);

            var script = document.createElement('script');
            script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
            script.onload = resolve;
            document.head.appendChild(script);
        });
    }

    loadLeaflet().then(function () {
        mapContainers.forEach(initMap);
    });

    function initMap(container) {
        var lat = parseFloat(container.dataset.centerLat) || 51.1657;
        var lng = parseFloat(container.dataset.centerLng) || 10.4515;
        var zoom = parseInt(container.dataset.zoom) || 14;

        var map = L.map(container).setView([lat, lng], zoom);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
            maxZoom: 19,
        }).addTo(map);

        // Load markers from JSON script tag
        var jsonEl = container.parentNode.querySelector('.bbf-area-map__markers');
        if (!jsonEl) return;

        var markers;
        try {
            markers = JSON.parse(jsonEl.textContent);
        } catch (e) {
            return;
        }

        var markerLayers = {};

        markers.forEach(function (m) {
            if (m.lat === null || m.lng === null) return;

            var icon = L.divIcon({
                className: 'bbf-map-marker',
                html: '<span style="display:block;width:24px;height:24px;background:#EF4444;border:3px solid #fff;border-radius:50%;box-shadow:0 2px 6px rgba(0,0,0,0.3);"></span>',
                iconSize: [24, 24],
                iconAnchor: [12, 12],
            });

            var leafletMarker = L.marker([m.lat, m.lng], { icon: icon }).addTo(map);

            var popupContent = '<strong>' + m.title + '</strong>';
            if (m.description) popupContent += '<br>' + m.description;
            if (m.image) popupContent = '<img src="' + m.image + '" alt="" style="width:100%;max-width:200px;border-radius:4px;margin-bottom:0.5rem;">' + popupContent;
            leafletMarker.bindPopup(popupContent);

            var groupKey = 'group-' + (m.group || 'none');
            if (!markerLayers[groupKey]) markerLayers[groupKey] = [];
            markerLayers[groupKey].push(leafletMarker);
        });

        // ── Group filter ──────────────────────────────────
        var filterBtns = container.parentNode.parentNode.querySelectorAll('.bbf-area-map__filter [data-filter]');
        filterBtns.forEach(function (btn) {
            btn.addEventListener('click', function () {
                filterBtns.forEach(function (b) { b.classList.remove('active'); });
                btn.classList.add('active');

                var filter = btn.dataset.filter;

                Object.keys(markerLayers).forEach(function (key) {
                    markerLayers[key].forEach(function (marker) {
                        if (filter === 'all' || key === filter) {
                            marker.addTo(map);
                        } else {
                            map.removeLayer(marker);
                        }
                    });
                });

                // Filter marker list too
                var listItems = container.parentNode.parentNode.querySelectorAll('.bbf-marker-item');
                listItems.forEach(function (item) {
                    if (filter === 'all' || item.dataset.group === filter) {
                        item.style.display = '';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        });

        // Fit bounds if markers exist
        if (markers.length > 0) {
            var validMarkers = markers.filter(function (m) { return m.lat !== null; });
            if (validMarkers.length > 1) {
                var bounds = L.latLngBounds(validMarkers.map(function (m) { return [m.lat, m.lng]; }));
                map.fitBounds(bounds, { padding: [30, 30] });
            }
        }
    }

})();
