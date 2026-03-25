/**
 * BBF Events – Area/Map Editor
 * Interactive marker placement for area maps in admin.
 */
(function () {
    'use strict';

    const mapCanvas = document.getElementById('bbf-area-map-editor');
    if (!mapCanvas) return;

    const mapType = mapCanvas.dataset.mapType;
    const staticImg = mapCanvas.dataset.staticImage;

    // ── Static image marker placement ─────────────────────
    if (mapType === 'static_image' && staticImg) {
        const img = document.createElement('img');
        img.src = staticImg;
        img.style.cssText = 'width:100%;display:block;';
        mapCanvas.appendChild(img);
        mapCanvas.style.position = 'relative';
        mapCanvas.style.cursor = 'crosshair';

        mapCanvas.addEventListener('click', function (e) {
            const rect = img.getBoundingClientRect();
            const x = ((e.clientX - rect.left) / rect.width * 100).toFixed(2);
            const y = ((e.clientY - rect.top) / rect.height * 100).toFixed(2);

            const posXInput = document.querySelector('input[name="pos_x"]');
            const posYInput = document.querySelector('input[name="pos_y"]');
            if (posXInput) posXInput.value = x;
            if (posYInput) posYInput.value = y;

            // Visual feedback
            mapCanvas.querySelectorAll('.bbf-temp-marker').forEach(function (m) { m.remove(); });
            const marker = document.createElement('div');
            marker.className = 'bbf-temp-marker';
            marker.style.cssText = `position:absolute;left:${x}%;top:${y}%;width:20px;height:20px;background:#EF4444;border:2px solid #fff;border-radius:50%;transform:translate(-50%,-50%);pointer-events:none;box-shadow:0 2px 4px rgba(0,0,0,0.3);`;
            mapCanvas.appendChild(marker);
        });
    }

    // ── Interactive map (Leaflet) ─────────────────────────
    if (mapType === 'interactive' && typeof L !== 'undefined') {
        const lat = parseFloat(mapCanvas.dataset.centerLat) || 51.1657;
        const lng = parseFloat(mapCanvas.dataset.centerLng) || 10.4515;
        const zoom = parseInt(mapCanvas.dataset.zoom) || 14;

        const map = L.map(mapCanvas).setView([lat, lng], zoom);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors',
        }).addTo(map);

        map.on('click', function (e) {
            const latInput = document.querySelector('input[name="lat"]');
            const lngInput = document.querySelector('input[name="lng"]');
            if (latInput) latInput.value = e.latlng.lat.toFixed(7);
            if (lngInput) lngInput.value = e.latlng.lng.toFixed(7);

            // Update center fields too
            const centerLatInput = document.querySelector('input[name="center_lat"]');
            const centerLngInput = document.querySelector('input[name="center_lng"]');
            if (centerLatInput && centerLatInput.value === '') centerLatInput.value = e.latlng.lat.toFixed(7);
            if (centerLngInput && centerLngInput.value === '') centerLngInput.value = e.latlng.lng.toFixed(7);
        });
    }

})();
