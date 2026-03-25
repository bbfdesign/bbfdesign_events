/**
 * BBF Events – Gallery (lazy loaded)
 * Simple lightbox for event image galleries.
 */
(function () {
    'use strict';

    const galleries = document.querySelectorAll('.bbf-gallery[data-lightbox="true"], .bbf-gallery');
    if (!galleries.length) return;

    // ── Lightbox overlay ──────────────────────────────────
    let overlay = null;
    let currentImages = [];
    let currentIndex = 0;

    function createOverlay() {
        overlay = document.createElement('div');
        overlay.className = 'bbf-lightbox';
        overlay.setAttribute('role', 'dialog');
        overlay.setAttribute('aria-label', 'Bildergalerie');
        overlay.innerHTML = `
            <div class="bbf-lightbox__backdrop"></div>
            <div class="bbf-lightbox__content">
                <img class="bbf-lightbox__img" src="" alt="">
                <button class="bbf-lightbox__close" aria-label="Schließen">&times;</button>
                <button class="bbf-lightbox__prev" aria-label="Vorheriges Bild">&lsaquo;</button>
                <button class="bbf-lightbox__next" aria-label="Nächstes Bild">&rsaquo;</button>
                <div class="bbf-lightbox__counter"></div>
            </div>
        `;

        const style = document.createElement('style');
        style.textContent = `
            .bbf-lightbox { position:fixed; inset:0; z-index:9999; display:flex; align-items:center; justify-content:center; }
            .bbf-lightbox__backdrop { position:absolute; inset:0; background:rgba(0,0,0,0.9); }
            .bbf-lightbox__content { position:relative; max-width:90vw; max-height:90vh; }
            .bbf-lightbox__img { max-width:90vw; max-height:85vh; object-fit:contain; display:block; }
            .bbf-lightbox__close { position:absolute; top:-2rem; right:0; background:none; border:none; color:#fff; font-size:2rem; cursor:pointer; }
            .bbf-lightbox__prev, .bbf-lightbox__next { position:absolute; top:50%; transform:translateY(-50%); background:none; border:none; color:#fff; font-size:3rem; cursor:pointer; padding:1rem; }
            .bbf-lightbox__prev { left:-3rem; }
            .bbf-lightbox__next { right:-3rem; }
            .bbf-lightbox__counter { text-align:center; color:#fff; margin-top:0.5rem; font-size:0.875rem; }
        `;
        document.head.appendChild(style);
        document.body.appendChild(overlay);

        overlay.querySelector('.bbf-lightbox__backdrop').addEventListener('click', close);
        overlay.querySelector('.bbf-lightbox__close').addEventListener('click', close);
        overlay.querySelector('.bbf-lightbox__prev').addEventListener('click', prev);
        overlay.querySelector('.bbf-lightbox__next').addEventListener('click', next);

        document.addEventListener('keydown', function (e) {
            if (!overlay || !overlay.parentNode) return;
            if (e.key === 'Escape') close();
            if (e.key === 'ArrowLeft') prev();
            if (e.key === 'ArrowRight') next();
        });
    }

    function open(images, index) {
        if (!overlay) createOverlay();
        currentImages = images;
        currentIndex = index;
        show();
    }

    function show() {
        const img = overlay.querySelector('.bbf-lightbox__img');
        img.src = currentImages[currentIndex];
        overlay.querySelector('.bbf-lightbox__counter').textContent =
            (currentIndex + 1) + ' / ' + currentImages.length;
        overlay.querySelector('.bbf-lightbox__prev').style.display = currentImages.length > 1 ? '' : 'none';
        overlay.querySelector('.bbf-lightbox__next').style.display = currentImages.length > 1 ? '' : 'none';
    }

    function close() {
        if (overlay && overlay.parentNode) {
            overlay.parentNode.removeChild(overlay);
            overlay = null;
        }
    }

    function prev() {
        currentIndex = (currentIndex - 1 + currentImages.length) % currentImages.length;
        show();
    }

    function next() {
        currentIndex = (currentIndex + 1) % currentImages.length;
        show();
    }

    // ── Bind galleries ────────────────────────────────────
    galleries.forEach(function (gallery) {
        const images = Array.from(gallery.querySelectorAll('img')).map(function (img) { return img.src; });

        gallery.querySelectorAll('img').forEach(function (img, idx) {
            img.style.cursor = 'pointer';
            img.addEventListener('click', function () {
                open(images, idx);
            });
        });
    });

})();
