<form class="yml-search" data-yml-search aria-label="{'yandexmapslocator_search_label' | lexicon}">
    <div class="yml-search__row">
        <input
            type="search"
            name="address"
            class="yml-search__input"
            placeholder="{'yandexmapslocator_address_placeholder' | lexicon}"
            aria-label="{'yandexmapslocator_address' | lexicon}"
            autocomplete="street-address"
            {if $address?}value="{$address | escape}"{/if}
        >
        <button type="submit" class="yml-search__submit" data-yml-submit>
            <span data-yml-submit-label>{'yandexmapslocator_find' | lexicon}</span>
        </button>
    </div>
    <div class="yml-search__tools" data-yml-search-tools>
        <button type="button" class="yml-search__locate" data-yml-locate>
            {'yandexmapslocator_locate_me' | lexicon}
        </button>
    </div>
    <p class="yml-search__error" data-yml-error hidden role="alert"></p>
</form>
