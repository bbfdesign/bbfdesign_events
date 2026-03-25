<div class="bbf-share" aria-label="Teilen">
    <span class="bbf-share__label">Teilen:</span>
    <div class="bbf-share__buttons">
        <a href="https://www.facebook.com/sharer/sharer.php?u={$event->url|escape:'url'}"
           target="_blank" rel="noopener" class="bbf-share__btn bbf-share__btn--facebook"
           aria-label="Auf Facebook teilen">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/>
            </svg>
        </a>
        <a href="https://twitter.com/intent/tweet?url={$event->url|escape:'url'}&text={$event->getTitle()|escape:'url'}"
           target="_blank" rel="noopener" class="bbf-share__btn bbf-share__btn--twitter"
           aria-label="Auf X teilen">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M23 3a10.9 10.9 0 01-3.14 1.53 4.48 4.48 0 00-7.86 3v1A10.66 10.66 0 013 4s-4 9 5 13a11.64 11.64 0 01-7 2c9 5 20 0 20-11.5a4.5 4.5 0 00-.08-.83A7.72 7.72 0 0023 3z"/>
            </svg>
        </a>
        <a href="mailto:?subject={$event->getTitle()|escape:'url'}&body={$event->url|escape:'url'}"
           class="bbf-share__btn bbf-share__btn--email"
           aria-label="Per E-Mail teilen">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                <polyline points="22,6 12,13 2,6"/>
            </svg>
        </a>
    </div>
</div>
