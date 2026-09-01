# YandexMapsLocator — локатор точек на Яндекс.Картах

**YandexMapsLocator** (Free) для **MODX Revolution 3**: карта, список точек, поиск по адресу и геолокация браузера. Одна точка = один опубликованный ресурс с TV (адрес, координаты, телефон, часы и др.).

Пространство имён **`yandexmapslocator`**, настройки **`yandexmapslocator_*`**. Чанки на **Fenom** (нужен **pdoTools**). Карта и геокодер — по одному ключу Яндекс.Карт.

Платное расширение **YandexMapsLocatorPro** ставится поверх Free: REST API v1 (включая `/meta`), «открыто сейчас» с TZ на точке и сети, `status_hint` / `closes_at`, фильтры amenity/brand, CSV + bulk geocode в CMP, карта самовывоза на карточке MiniShop3. Матрица: [Free и Pro](https://docs.modx.pro/components/yandexmapslocator/free-vs-pro).

## Документация

| Ресурс | Ссылка |
|--------|--------|
| Обзор | [docs.modx.pro/components/yandexmapslocator](https://docs.modx.pro/components/yandexmapslocator/) |
| Быстрый старт | [quick-start](https://docs.modx.pro/components/yandexmapslocator/quick-start) |
| Free и Pro | [free-vs-pro](https://docs.modx.pro/components/yandexmapslocator/free-vs-pro) |
| Сниппет | [snippets/YandexMapsLocator](https://docs.modx.pro/components/yandexmapslocator/snippets/YandexMapsLocator) |
| Настройки | [settings](https://docs.modx.pro/components/yandexmapslocator/settings) |
| Интерфейс | [frontend](https://docs.modx.pro/components/yandexmapslocator/frontend) |
| Интеграция | [integration](https://docs.modx.pro/components/yandexmapslocator/integration) |
| Contexts | [contexts](https://docs.modx.pro/components/yandexmapslocator/contexts) |
| Extension API | [extension-api](https://docs.modx.pro/components/yandexmapslocator/extension-api) |
| События | [events](https://docs.modx.pro/components/yandexmapslocator/events) |
| FAQ | [faq](https://docs.modx.pro/components/yandexmapslocator/faq) |
| Pro: обзор | [pro/](https://docs.modx.pro/components/yandexmapslocator/pro/) |
| Pro: REST API | [pro/api](https://docs.modx.pro/components/yandexmapslocator/pro/api) |
| Pro: безопасность API | [pro/api-security](https://docs.modx.pro/components/yandexmapslocator/pro/api-security) |
| Pro: «открыто сейчас» | [pro/working-now](https://docs.modx.pro/components/yandexmapslocator/pro/working-now) |
| Pro: CMP | [pro/manager](https://docs.modx.pro/components/yandexmapslocator/pro/manager) |
| Pro: MiniShop3 | [pro/minishop3](https://docs.modx.pro/components/yandexmapslocator/pro/minishop3) |
| Changelog (репозиторий) | [changelog.txt](../core/components/yandexmapslocator/docs/changelog.txt) |
| GitHub | [Ibochkarev/YandexMapsLocator](https://github.com/Ibochkarev/YandexMapsLocator) |

---

## Назначение

Дополнение для сайта на **MODX 3**, где нужна витрина сети магазинов, пунктов выдачи или филиалов:

- карта Яндекса с маркерами и кластеризацией;
- список точек рядом с картой (на мобильном — табы «Список» / «Карта»);
- поиск по адресу и сортировка по расстоянию;
- кнопка «Моё местоположение» / «Все точки»;
- маршрут в Яндекс Навигатор с карточки и из балуна;
- категории, кастомные иконки маркеров и картинки в балуне.

Точки вы редактируете как обычные ресурсы MODX. Отдельной таблицы магазинов в Free нет.

---

## Как работает

1. Создаёте контейнер (например «Магазины») и дочерние ресурсы-точки.
2. Заполняете TV адреса и координат. В форме ресурса есть кнопка геокода (plugin в mgr), если задан API-ключ.
3. На странице вызываете сниппет `YandexMapsLocator` с `parents` = ID контейнера.
4. Сниппет отдаёт HTML (чанки Fenom). JS синхронизирует список и маркеры. AJAX-поиск идёт в `search.php` (same-origin).
5. При установленном Pro UI может ходить в REST `api.php`, если `api_enabled` включён. Иначе остаётся `search.php`.

Без ключа Яндекс.Карт карта на фронте и серверное геокодирование не работают. Один ключ в `yandexmapslocator_api_key` используется и в браузере (JS API 2.1), и на сервере (HTTP Геокодер).

---

## Возможности (Free)

1. **Локатор на странице** — сниппет **`YandexMapsLocator`**: форма поиска, список, карта. Параметры `parents`, `limit`, `radius`, `sortby` (`pagetitle`, `distance`, …), `category`, `filters`, `context`, `return`.

2. **Режимы вывода** — `return=chunks` (HTML по умолчанию), `data` (плейсхолдеры `yandexmapslocator.stores` / `count`), `json` (`{ success, results }` для своего шаблона на том же сайте).

3. **Геолокация** — `navigator.geolocation`, поиск с `lat`/`lng` и `sortby=distance`. Повторный клик сбрасывает фильтр («Все точки»). На мобильном после locate открывается вкладка «Карта».

4. **Поиск по адресу** — геокодирование на сервере через Яндекс, затем выборка точек в радиусе.

5. **Карточка точки** — заголовок со ссылкой, адрес, расстояние, телефон, часы (`working_hours_compact` / HTML с выделенными днями), «Показать на карте», кнопка маршрута.

6. **Балун маркера** — адрес, телефон, часы, картинка (`yandexmaps_balloon_image` или fallback из настроек), маршрут. Хуки JS: `balloon:build`, `marker:options`.

7. **TV из коробки** — адрес, широта, долгота, телефон, email, часы работы, категория, balloon image, marker icon. Имена меняются через `yandexmapslocator_tv_*`.

8. **Геокод в менеджере** — кнопка «Получить координаты» под полем адреса в колонке значения TV (`.modx-tv-form-element`).

9. **Mobile-first UI** — BEM `yml-*`, контракт `data-yml-*`, CSS-переменные на `.yml-locator`, кэш-бастинг ассетов `?v=`. Балун: `balloonMaxWidth` 360.

10. **Multi-context** — параметр `context` / `ctx`, настройки `default_context` и `allowed_contexts`.

11. **Extension API** — события регистрации фильтров и feature providers, хуки prepare Store и search. Pro и сторонние extras подключаются без правок ядра Free.

12. **Часовой пояс сети** — `yandexmapslocator_timezone` (IANA, по умолчанию `Europe/Moscow`). Fallback для Pro, если у точки нет TV `yandexmaps_timezone`. Расписание в TV — местное время точки, не UTC PHP.

---

## Free и Pro

| | Free | Pro |
|--|------|-----|
| Карта, список, поиск, геолокация, маршрут | да | тот же UI |
| `search.php`, `return=chunks/data/json` | да | fallback при выключенном REST |
| Extension API | да | использует Free |
| REST `api.php` + `/meta` (CORS, Bearer, `fields`/`include`) | — | да |
| `working_now`, бейджи, `status_hint`, `closes_at` | — | да |
| TZ на точке (`yandexmaps_timezone`) | — | да |
| Фильтры `amenity`, `brand` | — | да |
| CSV + bulk geocode в CMP | — | да |
| MiniShop3 (`ms3_product_ids` / `productId`) | — | да |

`return=json` и `search.php` в Free не заменяют headless REST: нет CORS для чужого origin, нет whitelist полей и Bearer. Nuxt/Next — только Pro.

---

## Установка

1. Соберите или скачайте transport: `php _build/build.php`.
2. Установите через **Управление пакетами**.
3. Укажите ключ в **Система → Настройки системы → yandexmapslocator** → `yandexmapslocator_api_key`.
4. Создайте контейнер и дочерние точки, заполните TV.
5. Вставьте сниппет на страницу (нужен pdoTools / Fenom).

Ключ: [Кабинет разработчика Яндекса](https://developer.tech.yandex.ru/), продукт **JavaScript API и HTTP Геокодер**. В кабинете ограничьте ключ по HTTP Referer и по IP для серверного геокодера. Не коммитьте ключ в Git.

---

## Сниппет YandexMapsLocator

### Минимальный вызов

```fenom
{'!YandexMapsLocator' | snippet : [
    'parents' => 123,
    'radius' => 50,
    'sortby' => 'distance'
]}
```

### Ближайшие от координат

```fenom
{'!YandexMapsLocator' | snippet : [
    'parents' => 42,
    'latitude' => 55.03,
    'longitude' => 82.92,
    'radius' => 30,
    'sortby' => 'distance',
    'limit' => 20
]}
```

### Фильтр по категории

```fenom
{'!YandexMapsLocator' | snippet : [
    'parents' => 42,
    'category' => 'аптека',
    'filters' => 'category'
]}
```

### JSON для своего фронта (same-origin)

```fenom
{'!YandexMapsLocator' | snippet : [
    'parents' => 42,
    'return' => 'json'
]}
```

### Только открытые сейчас (нужен Pro)

```fenom
{'!YandexMapsLocator' | snippet : [
    'parents' => 42,
    'filters' => 'working_now'
]}
```

На точке можно задать TV `yandexmaps_timezone` (IANA). Иначе берётся сеть `yandexmapslocator_timezone` (например `Asia/Omsk`). В TV часов для фильтра нужен JSON-расписание. Произвольный текст показывается в карточке, но «открыто сейчас» для него не считается.

Полный список параметров: [сниппет YandexMapsLocator](https://docs.modx.pro/components/yandexmapslocator/snippets/YandexMapsLocator).

Чанки по умолчанию: `yandexmapslocator.outer`, `.search`, `.store`, `.empty`, `.error`.

---

## TV по умолчанию

| TV | Назначение |
|----|------------|
| `yandexmaps_address` | Адрес |
| `yandexmaps_latitude` | Широта |
| `yandexmaps_longitude` | Долгота |
| `yandexmaps_phone` | Телефон |
| `yandexmaps_email` | Email |
| `yandexmaps_working_hours` | Часы (текст или JSON) |
| `yandexmaps_category` | Категория |
| `yandexmaps_balloon_image` | Картинка в балуне |
| `yandexmaps_marker_icon` | Иконка маркера |

---

## JavaScript API

```javascript
const locator = new YandexMapsLocator('[data-yml-root]', { apiUrl, config, stores });
locator.search({ address: 'Омск, ул. Ленина, 25' });
locator.locate();
locator.on('store:click', ({ id }) => console.log(id));
```

События: `store:click`, `marker:click`, `balloon:build`, `search:start`, `search:complete`, `error`. Селекторы — в `js/modules/dom.js`, опирайтесь на `data-yml-*`, не на классы.

---

## Системные настройки (основные)

| Ключ | Назначение |
|------|------------|
| `yandexmapslocator_api_key` | Ключ JS API и Геокодера |
| `yandexmapslocator_default_zoom` / `_latitude` / `_longitude` | Центр и масштаб карты |
| `yandexmapslocator_cluster` | Кластеризация маркеров |
| `yandexmapslocator_default_radius` | Радиус поиска, км |
| `yandexmapslocator_distance_unit` | `km` или `m` |
| `yandexmapslocator_timezone` | IANA-таймзона сети (fallback, если у точки нет TV timezone) |
| `yandexmapslocator_default_context` / `_allowed_contexts` | Multi-context |
| `yandexmapslocator_tv_*` | Имена TV |
| `yandexmapslocator_api_*` | Rate limit, CORS, token, kill switch (REST активен после Pro) |

---

## Требования

- MODX Revolution 3.x
- PHP 8.2+
- MySQL / MariaDB
- [pdoTools](https://modx.pro/components/pdotools) (Fenom-чанки)
- API-ключ [Яндекс.Карт](https://developer.tech.yandex.ru/) (JavaScript API и HTTP Геокодер)

Лицензия: MIT.

## Скриншоты

Файлы кладите в `_preview/`. Где снимать: [README.md](README.md#скриншоты).

| Файл | Кадр |
|------|------|
| `shot-01-locator-desktop.png` | Desktop: список + карта |
| `shot-02-locator-mobile.png` | Mobile: табы |
| `shot-03-search-locate.png` | Поиск и геолокация |
| `shot-04-store-card.png` | Карточка точки |
| `shot-05-mgr-geocode.png` | Геокод в mgr |

![Локатор desktop](shot-01-locator-desktop.png)

Снять: стенд `yandexmapslocator/?yml_parents=…`, ширина ≥769px.

![Локатор mobile](shot-02-locator-mobile.png)

Снять: тот же URL, узкий viewport.

![Поиск и геолокация](shot-03-search-locate.png)

![Карточка точки](shot-04-store-card.png)

![Геокод в менеджере](shot-05-mgr-geocode.png)
