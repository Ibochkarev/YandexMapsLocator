<article
    class="yml-store"
    data-yml-store-id="{$id}"
    data-yml-lat="{$latitude}"
    data-yml-lng="{$longitude}"
    role="listitem"
    aria-labelledby="yml-store-title-{$id}"
    {if $idx == 1}data-yml-active aria-current="true"{/if}
>
    <h3 class="yml-store__title">
        <a id="yml-store-title-{$id}" href="{$url}">{$pagetitle}</a>
    </h3>
    {if $address?}
        <p class="yml-store__address">{$address}</p>
    {/if}
    {if $distance_formatted?}
        <p class="yml-store__distance">{$distance_formatted}</p>
    {/if}
    {if $phone?}
        <p class="yml-store__phone"><a href="tel:{$phone}">{$phone}</a></p>
    {/if}
    {set $ymlHours = $working_hours_compact ?: ($working_hours_formatted ?: '')}
    {set $ymlHoursTrim = $ymlHours | trim}
    {set $ymlHoursHtml = $working_hours_compact_html ?: ''}
    {set $ymlOpenLex = 'yandexmapslocator_open_now' | lexicon}
    {set $ymlClosedLex = 'yandexmapslocator_closed_now' | lexicon}
    {set $ymlShowHours = $ymlHoursTrim && $ymlHoursTrim != $ymlOpenLex && $ymlHoursTrim != $ymlClosedLex && $ymlHoursTrim != 'Закрыто' && $ymlHoursTrim != 'Closed'}
    {if isset($is_open_now) || $ymlShowHours}
        <div class="yml-store__meta">
            {if isset($is_open_now)}
                <span class="yml-store__status {if $is_open_now}is-open{else}is-closed{/if}">
                    {if $is_open_now}{$ymlOpenLex}{else}{$ymlClosedLex}{/if}
                </span>
            {/if}
            {if $status_hint?}
                <span class="yml-store__status-hint">{$status_hint}</span>
            {/if}
            {if $ymlShowHours}
                <p class="yml-store__hours">{if $ymlHoursHtml}{raw $ymlHoursHtml}{else}{$ymlHoursTrim}{/if}</p>
            {/if}
        </div>
    {/if}
    <div class="yml-store__actions">
        <button type="button" class="yml-store__select" data-yml-select>
            {'yandexmapslocator_show_on_map' | lexicon}
        </button>
        <a
            class="yml-store__route"
            href="https://yandex.ru/maps/?rtext=~{$latitude},{$longitude}&rtt=auto"
            target="_blank"
            rel="noopener noreferrer"
            aria-label="{'yandexmapslocator_route' | lexicon}"
            title="{'yandexmapslocator_route' | lexicon}"
        >
            <img
                class="yml-store__route-icon"
                src="{$_modx->config['assets_url']}components/yandexmapslocator/img/yandex-navigator.svg"
                width="20"
                height="20"
                alt=""
                decoding="async"
            >
        </a>
    </div>
</article>
