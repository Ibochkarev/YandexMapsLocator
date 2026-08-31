<div

    class="yml-locator"

    data-yml-root

    data-yml-view="list"

    {if $is_empty?}data-yml-empty{/if}

    {if $parents?}data-yml-parents="{$parents}"{/if}

>

    <div class="yml-locator__search">{$search}</div>

    <div class="yml-locator__layout">

        <div class="yml-locator__view-tabs" role="tablist" aria-label="{'yandexmapslocator_view_toggle' | lexicon}">

            <button

                type="button"

                class="yml-locator__view-tab"

                id="yml-tab-list"

                data-yml-view-tab="list"

                data-yml-selected

                role="tab"

                aria-selected="true"

                aria-controls="yml-panel-list"

                tabindex="0"

            >{'yandexmapslocator_list' | lexicon}</button>

            <button

                type="button"

                class="yml-locator__view-tab"

                id="yml-tab-map"

                data-yml-view-tab="map"

                role="tab"

                aria-selected="false"

                aria-controls="yml-panel-map"

                tabindex="-1"

            >{'yandexmapslocator_map' | lexicon}</button>

        </div>

        <div

            class="yml-locator__panel"

            data-yml-panel="list"

            id="yml-panel-list"

            role="tabpanel"

            aria-labelledby="yml-tab-list"

        >

            <div class="yml-locator__list" data-yml-list role="list" aria-label="{'yandexmapslocator_stores_list' | lexicon}">{$stores}</div>

        </div>

        <div

            class="yml-locator__map"

            data-yml-map

            data-yml-panel="map"

            id="yml-panel-map"

            role="tabpanel"

            aria-labelledby="yml-tab-map"

            tabindex="-1"

        ></div>

    </div>

    <script type="application/json" data-yml-config>{$map_config}</script>

    <script type="application/json" data-yml-stores>{$stores_json}</script>

    <link rel="stylesheet" href="{$assets_url}css/locator.css?v={$assets_version}">

    <script type="module" src="{$assets_url}js/locator.js?v={$assets_version}"></script>

</div>

