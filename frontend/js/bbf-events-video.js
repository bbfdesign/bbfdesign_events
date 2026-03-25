/**
 * BBF Events – Video Embed (lazy loaded, consent-aware)
 * Implements facade pattern: shows a poster/placeholder until user clicks.
 * Supports YouTube, Vimeo, and local videos.
 */
(function () {
    'use strict';

    var videoBlocks = document.querySelectorAll('.bbf-video-block[data-video-url]');
    if (!videoBlocks.length) return;

    videoBlocks.forEach(function (block) {
        var source = block.dataset.videoSource || 'youtube';
        var url = block.dataset.videoUrl || '';
        var poster = block.dataset.videoPoster || '';
        var consentRequired = block.dataset.consentRequired !== 'false';

        if (!url) return;

        var facade = block.querySelector('.bbf-video-block__facade');
        if (!facade) {
            facade = document.createElement('div');
            facade.className = 'bbf-video-block__facade';
            block.appendChild(facade);
        }

        // Build facade
        facade.style.cssText = 'position:relative;cursor:pointer;aspect-ratio:16/9;background:#000;display:flex;align-items:center;justify-content:center;border-radius:0.5rem;overflow:hidden;';

        if (poster) {
            facade.style.backgroundImage = 'url(' + poster + ')';
            facade.style.backgroundSize = 'cover';
            facade.style.backgroundPosition = 'center';
        }

        facade.innerHTML = `
            <div style="position:absolute;inset:0;background:rgba(0,0,0,0.3);"></div>
            <div style="position:relative;z-index:1;text-align:center;color:#fff;">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="currentColor" style="opacity:0.9;"><path d="M8 5v14l11-7z"/></svg>
                ${consentRequired ? '<p style="font-size:0.8125rem;margin-top:0.5rem;opacity:0.8;">Klicken zum Laden des Videos</p>' : ''}
            </div>
        `;

        facade.addEventListener('click', function () {
            loadVideo(block, source, url);
        });
    });

    function loadVideo(block, source, url) {
        var iframe;

        if (source === 'youtube') {
            var ytId = extractYouTubeId(url);
            if (!ytId) return;
            iframe = document.createElement('iframe');
            iframe.src = 'https://www.youtube-nocookie.com/embed/' + ytId + '?autoplay=1&rel=0';
            iframe.allow = 'autoplay; encrypted-media';
        } else if (source === 'vimeo') {
            var vimeoId = extractVimeoId(url);
            if (!vimeoId) return;
            iframe = document.createElement('iframe');
            iframe.src = 'https://player.vimeo.com/video/' + vimeoId + '?autoplay=1';
            iframe.allow = 'autoplay; fullscreen';
        } else if (source === 'local') {
            var video = document.createElement('video');
            video.src = url;
            video.controls = true;
            video.autoplay = true;
            video.style.cssText = 'width:100%;border-radius:0.5rem;';
            video.setAttribute('playsinline', '');
            block.innerHTML = '';
            block.appendChild(video);
            return;
        }

        if (iframe) {
            iframe.style.cssText = 'width:100%;aspect-ratio:16/9;border:none;border-radius:0.5rem;';
            iframe.setAttribute('allowfullscreen', '');
            iframe.title = 'Video';
            block.innerHTML = '';
            block.appendChild(iframe);
        }
    }

    function extractYouTubeId(url) {
        var match = url.match(/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/);
        return match ? match[1] : null;
    }

    function extractVimeoId(url) {
        var match = url.match(/vimeo\.com\/(\d+)/);
        return match ? match[1] : null;
    }

})();
