<div class="bbf-filter-bar" role="search" aria-label="Veranstaltungen filtern">
    <form method="get" action="{$listingUrl}" class="bbf-filter-bar__form">

        <div class="bbf-filter-bar__group">
            <label for="bbf-filter-category" class="bbf-filter-bar__label">Kategorie</label>
            <select id="bbf-filter-category" name="category" class="form-select bbf-filter-bar__select">
                <option value="">Alle Kategorien</option>
                {foreach $categories as $cat}
                    <option value="{$cat->slug}"{if $filter->categorySlug === $cat->slug} selected{/if}>
                        {$cat->getName()}
                    </option>
                {/foreach}
            </select>
        </div>

        <div class="bbf-filter-bar__group">
            <label for="bbf-filter-status" class="bbf-filter-bar__label">Zeitraum</label>
            <select id="bbf-filter-status" name="status" class="form-select bbf-filter-bar__select">
                <option value="upcoming"{if $filter->temporalStatus === 'upcoming'} selected{/if}>Kommende</option>
                <option value="past"{if $filter->temporalStatus === 'past'} selected{/if}>Vergangene</option>
                <option value="all"{if $filter->temporalStatus === 'all'} selected{/if}>Alle</option>
            </select>
        </div>

        <div class="bbf-filter-bar__group">
            <label for="bbf-filter-sort" class="bbf-filter-bar__label">Sortierung</label>
            <select id="bbf-filter-sort" name="sort" class="form-select bbf-filter-bar__select">
                <option value="date_asc"{if $filter->sortBy === 'date_asc'} selected{/if}>Datum aufsteigend</option>
                <option value="date_desc"{if $filter->sortBy === 'date_desc'} selected{/if}>Datum absteigend</option>
                <option value="title"{if $filter->sortBy === 'title'} selected{/if}>Titel</option>
                <option value="featured"{if $filter->sortBy === 'featured'} selected{/if}>Empfohlen</option>
            </select>
        </div>

        <div class="bbf-filter-bar__group bbf-filter-bar__group--search">
            <label for="bbf-filter-search" class="bbf-filter-bar__label">Suche</label>
            <input type="search" id="bbf-filter-search" name="q"
                   value="{$filter->searchQuery|escape:'html'}"
                   placeholder="Veranstaltung suchen..."
                   class="form-control bbf-filter-bar__input">
        </div>

        <div class="bbf-filter-bar__actions">
            <button type="submit" class="btn btn-primary bbf-filter-bar__submit">Filtern</button>
            <a href="{$listingUrl}" class="btn btn-outline-secondary bbf-filter-bar__reset">Zurücksetzen</a>
        </div>

    </form>
</div>
