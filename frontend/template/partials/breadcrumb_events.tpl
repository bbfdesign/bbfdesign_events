<nav aria-label="Breadcrumb" class="bbf-breadcrumb">
    <ol class="breadcrumb" itemscope itemtype="https://schema.org/BreadcrumbList">
        <li class="breadcrumb-item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
            <a href="/" itemprop="item"><span itemprop="name">Startseite</span></a>
            <meta itemprop="position" content="1">
        </li>
        <li class="breadcrumb-item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
            <a href="/veranstaltungen" itemprop="item"><span itemprop="name">Veranstaltungen</span></a>
            <meta itemprop="position" content="2">
        </li>
        {if isset($category)}
            <li class="breadcrumb-item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                <a href="/veranstaltungen/kategorie/{$category->slug}" itemprop="item"><span itemprop="name">{$category->getName()}</span></a>
                <meta itemprop="position" content="3">
            </li>
        {/if}
        {if isset($event)}
            <li class="breadcrumb-item active" aria-current="page" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                <span itemprop="name">{$event->getTitle()}</span>
                <meta itemprop="position" content="{if isset($category)}4{else}3{/if}">
            </li>
        {/if}
        {if isset($isArchive) && $isArchive}
            <li class="breadcrumb-item active" aria-current="page" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                <span itemprop="name">Archiv</span>
                <meta itemprop="position" content="3">
            </li>
        {/if}
    </ol>
</nav>
