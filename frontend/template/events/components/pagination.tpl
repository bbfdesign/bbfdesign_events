{if $pagination->totalPages > 1}
<nav class="bbf-pagination" aria-label="Seitennavigation">
    <ul class="pagination justify-content-center">
        {if $pagination->hasPreviousPage()}
            <li class="page-item">
                <a class="page-link" href="?page={$pagination->page - 1}" rel="prev" aria-label="Vorherige Seite">
                    &laquo;
                </a>
            </li>
        {else}
            <li class="page-item disabled">
                <span class="page-link">&laquo;</span>
            </li>
        {/if}

        {for $i=1 to $pagination->totalPages}
            {if $i === $pagination->page}
                <li class="page-item active" aria-current="page">
                    <span class="page-link">{$i}</span>
                </li>
            {elseif $i <= 2 || $i >= $pagination->totalPages - 1 || ($i >= $pagination->page - 1 && $i <= $pagination->page + 1)}
                <li class="page-item">
                    <a class="page-link" href="?page={$i}">{$i}</a>
                </li>
            {elseif $i === 3 && $pagination->page > 4}
                <li class="page-item disabled"><span class="page-link">...</span></li>
            {elseif $i === $pagination->totalPages - 2 && $pagination->page < $pagination->totalPages - 3}
                <li class="page-item disabled"><span class="page-link">...</span></li>
            {/if}
        {/for}

        {if $pagination->hasNextPage()}
            <li class="page-item">
                <a class="page-link" href="?page={$pagination->page + 1}" rel="next" aria-label="Nächste Seite">
                    &raquo;
                </a>
            </li>
        {else}
            <li class="page-item disabled">
                <span class="page-link">&raquo;</span>
            </li>
        {/if}
    </ul>

    <p class="bbf-pagination__info text-center text-muted">
        Seite {$pagination->page} von {$pagination->totalPages} ({$pagination->total} Veranstaltungen)
    </p>
</nav>
{/if}
