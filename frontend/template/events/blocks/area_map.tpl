<section class="bbf-area-section">
    <div class="container">
        {foreach $areaMaps as $map}
            <div class="bbf-area-map mb-4">
                {if $map->getTitle()}
                    <h3 class="mb-3">{$map->getTitle()}</h3>
                {/if}
                {if $map->getDescription()}
                    <p class="text-muted mb-3">{$map->getDescription()}</p>
                {/if}

                {if $showGroupFilter && !empty($map->markerGroups)}
                    <div class="bbf-area-map__filter mb-3 d-flex flex-wrap gap-2">
                        <button class="btn btn-sm btn-outline-secondary active" data-filter="all">Alle</button>
                        {foreach $map->markerGroups as $group}
                            <button class="btn btn-sm btn-outline-secondary" data-filter="group-{$group->id}" style="border-color: {$group->color}; color: {$group->color};">
                                {if $group->icon}<i class="fa {$group->icon}"></i>{/if}
                                {$group->getName()}
                            </button>
                        {/foreach}
                    </div>
                {/if}

                {if $map->isInteractive()}
                    <div class="bbf-area-map__canvas"
                         data-map-type="interactive"
                         data-center-lat="{$map->centerLat}"
                         data-center-lng="{$map->centerLng}"
                         data-zoom="{$map->zoomLevel}"
                         style="height: {$mapHeight}; border-radius: 0.5rem; overflow: hidden;">
                    </div>
                    <script type="application/json" class="bbf-area-map__markers">
                    {literal}[{/literal}
                    {foreach $map->markers as $marker}
                        {ldelim}"id":{$marker->id},"lat":{$marker->lat|default:'null'},"lng":{$marker->lng|default:'null'},"title":"{$marker->getTitle()|escape:'javascript'}","description":"{$marker->getDescription()|escape:'javascript'}","group":{$marker->groupId|default:'null'}{if $marker->image},"image":"{$marker->image}"{/if}{rdelim}{if !$marker@last},{/if}
                    {/foreach}
                    {literal}]{/literal}
                    </script>
                {elseif $map->isStaticImage() && $map->staticImage}
                    <div class="bbf-area-map__static position-relative" style="height: {$mapHeight};">
                        <img src="{$map->staticImage}" alt="{$map->getTitle()|escape:'html'}" class="w-100 h-100" style="object-fit: contain;">
                        {foreach $map->markers as $marker}
                            {if $marker->hasImagePosition()}
                                <div class="bbf-area-map__pin" style="position:absolute;left:{$marker->posX}%;top:{$marker->posY}%;transform:translate(-50%,-100%);" title="{$marker->getTitle()|escape:'html'}">
                                    <span style="display:inline-block;width:24px;height:24px;background:{if $marker->group}{$marker->group->color}{else}#EF4444{/if};border-radius:50%;border:2px solid #fff;cursor:pointer;"></span>
                                </div>
                            {/if}
                        {/foreach}
                    </div>
                {/if}

                {if $showMarkerList && !empty($map->markers)}
                    <div class="bbf-area-map__list mt-3">
                        <div class="row g-2">
                            {foreach $map->markers as $marker}
                                <div class="col-md-6 col-lg-4 bbf-marker-item" data-group="group-{$marker->groupId}">
                                    <div class="d-flex gap-2 align-items-start p-2 border rounded">
                                        {if $marker->image}
                                            <img src="{$marker->image}" alt="" style="width:40px;height:40px;object-fit:cover;border-radius:0.25rem;" loading="lazy">
                                        {/if}
                                        <div>
                                            <strong class="d-block">{$marker->getTitle()}</strong>
                                            {if $marker->getDescription()}<small class="text-muted">{$marker->getDescription()|truncate:60}</small>{/if}
                                        </div>
                                    </div>
                                </div>
                            {/foreach}
                        </div>
                    </div>
                {/if}
            </div>
        {/foreach}
    </div>
</section>
