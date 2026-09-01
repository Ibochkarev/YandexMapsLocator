# Интерфейс локатора

Фронт: Fenom chunks, `locator.css`, ES-модули. Разметка отделена от поведения: BEM-классы для вида, `data-yml-*` для JS и состояния.

## Принципы

### Mobile-first

На узком экране: одна колонка, табы «Список» / «Карта», поля поиска в ряд. С `@media (min-width: 48.0625rem)` (769px) включается двухколоночный layout: табы скрыты, список и карта видны сразу.

Табы лежат на уровне `.yml-locator__layout`, не внутри панели списка. После перехода на «Карта» табы остаются видимыми.

Карта на мобильном не выкидывается из DOM. В режиме списка JS ставит атрибут `hidden` на панель карты. Перед открытием балуна `ensureMapVisible()` переключает вид на «Карта».

### BEM

| Блок | Назначение |
|------|------------|
| `yml-locator` | Корень, design tokens |
| `yml-search` | Форма поиска |
| `yml-store` | Карточка точки |
| `yml-balloon` | HTML внутри балуна карты |

Элементы: `yml-locator__layout`, `yml-store__title`, `yml-search__input` и т.д. CSS-модификаторы состояния (`is-active`, `--map-view`) не используются. Состояние задаётся data-атрибутами.

### data-yml-* (контракт)

JS и CSS читают один набор атрибутов. Селекторы в `js/modules/dom.js`.

| Атрибут | Где | Назначение |
|---------|-----|------------|
| `data-yml-root` | `.yml-locator` | Корень, инициализация |
| `data-yml-view="list\|map"` | корень | Режим на мобильном |
| `data-yml-empty` | корень | Пустой список (SSR и после search) |
| `data-yml-located` | корень | Активен геофильтр после `locate()` |
| `data-yml-parents` | корень | ID родителей для поиска |
| `data-yml-config` | `<script type="application/json">` | map_config |
| `data-yml-stores` | `<script type="application/json">` | начальный список |
| `data-yml-search` | `<form>` | форма |
| `data-yml-submit` | кнопка «Найти» | |
| `data-yml-locate` | «Моё местоположение» / «Все точки» | |
| `data-yml-error` | блок ошибки | |
| `data-yml-list` | контейнер списка | |
| `data-yml-map` | контейнер карты | |
| `data-yml-panel="list\|map"` | панели | tabpanel |
| `data-yml-view-tab="list\|map"` | таб | |
| `data-yml-selected` | активный таб | boolean-присутствие |
| `data-yml-store-id` | карточка | ID точки |
| `data-yml-active` | выбранная карточка | |
| `data-yml-lat`, `data-yml-lng` | карточка | координаты |
| `data-yml-select` | «Показать на карте» | |

Примеры CSS:

```css
.yml-locator[data-yml-view='map'] .yml-locator__panel[data-yml-panel='list'] { display: none; }
.yml-locator[data-yml-empty] .yml-locator__map { opacity: 0.72; }
.yml-store[data-yml-active] { … }
.yml-locator__view-tab[data-yml-selected] { background: var(--yml-color-surface); }
```

Свой JS: `document.querySelector('[data-yml-root]')`, события `store:click`, `marker:click` без привязки к классам.

### CSS-переменные

Объявлены на `.yml-locator`. Переопределите на обёртке страницы:

```css
.my-page .yml-locator {
    --yml-color-accent: #c41e3a;
    --yml-layout-min-height: 24rem;
}
```

| Переменная | По умолчанию | Назначение |
|------------|--------------|------------|
| `--yml-color-text` | `#0f172a` | основной текст |
| `--yml-color-muted` | `#64748b` | адрес, часы |
| `--yml-color-accent` | `#2563eb` | акцент, кнопки |
| `--yml-color-accent-tint` | `#eff6ff` | фон активной карточки |
| `--yml-color-border` | `#e2e8f0` | границы |
| `--yml-color-surface` | `#fff` | фон карточек |
| `--yml-color-error` | `#b91c1c` | ошибки формы |
| `--yml-space-sm` … `--yml-space-xl` | 0.5–1.5rem | отступы |
| `--yml-radius-sm`, `--yml-radius-md` | 0.375 / 0.5rem | скругления |
| `--yml-font-size-sm` … `--yml-font-size-lg` | 0.8125–1rem | типографика |
| `--yml-control-height` | `2.5rem` | высота контролов поиска |
| `--yml-layout-height` | `clamp(20rem, 62vh, 36rem)` | высота карты/списка на desktop |
| `--yml-layout-min-height` | `20rem` | мин. высота |
| `--yml-list-sidebar-min` | `17.5rem` | мин. ширина списка (desktop) |
| `--yml-breakpoint-md` | `48.0625rem` | брейкпоинт (справочно) |

## Компоновка

```
┌─────────────────────────────────────────────┐
│  yml-search: адрес, «Найти», геолокация     │
├──────────────────┬──────────────────────────┤
│  [Список | Карта]  (табы, только mobile)    │
│  yml-locator__list (карточки yml-store)     │  yml-locator__map
└──────────────────┴──────────────────────────┘
        mobile: табы всегда видны; одна панель (list или map)
        desktop: обе колонки, табы скрыты
```

CSS и JS подключаются с `?v={$assets_version}` (версия пакета), чтобы браузер не держал старый кэш.

## Карточка (`yandexmapslocator.store`)

Chunk и `StoreList.js` совпадают по разметке.

| Элемент BEM | Поле / поведение |
|-------------|------------------|
| `yml-store__title` | `pagetitle`, `url` |
| `yml-store__address` | `address` |
| `yml-store__distance` | `distance_formatted` |
| `yml-store__phone` | `phone` |
| `yml-store__meta` | колонка: статус, подсказка, часы |
| `yml-store__status` | Pro: «Открыто» / «Закрыто» (`is_open_now`) |
| `yml-store__status-hint` | Pro: `status_hint` (например «до 21:00» / «откроется в …») |
| `yml-store__hours` | `working_hours_compact`. Дни в `.yml-store__hours-day` |
| `yml-store__select` | «Показать на карте», ширина по тексту |
| `yml-store__route` | квадрат 32×32, иконка Яндекс Навигатора (`img/yandex-navigator.svg`) |

Выбор на карте: только `[data-yml-select]`. Ссылки заголовка, телефона и маршрута не перехватываются.

## Поиск и действия под формой

Строка адреса + «Найти». Ниже `.yml-search__tools`: «Моё местоположение» и (в Pro) «Только открытые» в одном flex-ряду.

| Атрибут | Элемент |
|---------|---------|
| `data-yml-search-tools` | контейнер вторичных действий |
| `data-yml-locate` | геолокация / «Все точки» |
| `data-yml-open-now` | Pro: переключатель `working_now` |

## Геолокация

1. Клик по `[data-yml-locate]` → `navigator.geolocation` → search с `lat`/`lng` и `sortby=distance`.
2. На мобильном открывается вкладка «Карта».
3. Подпись кнопки меняется на «Все точки» (`data-yml-located` на корне).
4. Повторный клик сбрасывает геофильтр, подгружает полный список и возвращает вид «Список».
5. Поиск по адресу тоже снимает режим геолокации.

Тексты: `map_config.i18n.locateMe`, `map_config.i18n.showAll`.

## Балун маркера

Сборка в `js/modules/balloon.js`. Поля: изображение (`balloon_image`), адрес, расстояние, телефон, часы, маршрут. Классы `yml-balloon__*`. Ширина балуна: `balloonMaxWidth` 360 (чтобы кнопка маршрута и картинка не обрезались).

### Изображение и иконка

| Источник | Поле / настройка | Назначение |
|----------|------------------|------------|
| TV `yandexmaps_balloon_image` | `store.balloon_image` | Картинка в popup балуна |
| TV `yandexmaps_marker_icon` | `store.marker_icon` | Кастомная иконка маркера |
| System setting `default_balloon_image` | `map_config.balloon.defaultImage` | Fallback, если у точки нет TV |
| System setting `marker_icon_size` | `map_config.markerIconSize` | Размер кастомной иконки, px (`32,32`) |

Имена TV: settings `tv_balloon_image` / `tv_marker_icon`.

### JS-хуки

```javascript
locator.on('balloon:build', ({ store, properties }) => {
    // properties.balloonContentBody — изменить HTML балуна
});

locator.on('marker:options', ({ store, options }) => {
    // options — опции Placemark Yandex Maps
});
```

Событие MODX `OnYandexMapsLocatorAfterStorePrepare` — задать `balloon_image` / `marker_icon` через `Store::withExtra()` или поля DTO.

## Разделение Free / Pro

Полная матрица: [free-vs-pro.md](free-vs-pro.md).

| | Free | Pro |
|--|------|-----|
| `search.php` | AJAX `locator.js` (same-origin) | fallback |
| REST `api.php` | — | `?route=api/v1/…` |
| Бейдж «Открыто» + «Только открытые» | — | `pro.js` + `is_open_now` / `status_hint` |
| `working_now` | — | фильтр. TZ: TV `yandexmaps_timezone` или `yandexmapslocator_timezone` |

При Pro `map_config.restApi = true`, `Search.js` ходит в REST. Без Pro фильтр `working_now` не регистрируется.

## Форма и ошибки

`[data-yml-error]`, `role="alert"`. При запросе: `aria-busy` на корне, disabled у submit и locate. Тексты из `map_config.i18n`.

Пустой результат: chunk `yandexmapslocator.empty` (`role="status"`), на корне `data-yml-empty`.

## Доступность

| Область | Реализация |
|---------|------------|
| Фокус | `:focus-visible` на интерактивах |
| Список | `role="list"` / `listitem`, `aria-current` + `data-yml-active` |
| Табы | `aria-controls`, стрелки, `tabpanel`, `hidden` на неактивной панели (mobile) |
| Карточка | без `tabindex` на `<article>` |
| Маршрут | `aria-label` / `title` из lexicon `yandexmapslocator_route` |

## Кастомизация

| Задача | Как |
|--------|-----|
| Внешний вид | CSS-переменные или переопределение BEM в теме |
| Разметка карточки | chunk `yandexmapslocator.store`, сохраните `data-yml-*` |
| Свой JS | `SELECTORS` из `dom.js`, не опирайтесь на классы |
| Поля балуна | событие `balloon:build` или правка `balloon.js` |

## Миграция (breaking)

Раньше были длинные классы `yandexmapslocator-*` и `data-yandexmapslocator-*`. Замените:

| Было | Стало |
|------|-------|
| `[data-yandexmapslocator-root]` | `[data-yml-root]` |
| `.yandexmapslocator--map-view` | `[data-yml-view="map"]` на корне |
| `.is-active` на карточке | `[data-yml-active]` |
| `data-store-id` | `data-yml-store-id` |
| `data-store-select` | `data-yml-select` |

После обновления пакета очистите кэш MODX (chunks) и сделайте hard refresh.

## Файлы

| Путь | Роль |
|------|------|
| `elements/chunks/*.tpl` | SSR-разметка |
| `css/locator.css` | mobile-first, tokens, BEM |
| `img/yandex-navigator.svg` | иконка кнопки маршрута |
| `js/modules/dom.js` | BEM + SELECTORS |
| `js/locator.js` | оркестрация |
| `js/modules/StoreList.js` | список |
| `js/modules/balloon.js` | балун |
