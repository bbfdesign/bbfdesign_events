{if $status === 'running'}
    <span class="bbf-status-badge bbf-status-badge--running" aria-label="Läuft gerade">
        Läuft gerade
    </span>
{elseif $status === 'past'}
    <span class="bbf-status-badge bbf-status-badge--past" aria-label="Vergangen">
        Vergangen
    </span>
{/if}
