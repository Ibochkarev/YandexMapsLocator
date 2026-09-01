# YandexMapsLocator

Локатор точек для MODX Revolution 3 на картах Яндекса. Free: полный локатор на сайте без искусственных лимитов. Одна точка = один ресурс MODX.

Сравнение с Pro: [docs/free-vs-pro.md](docs/free-vs-pro.md). Документация: [docs/index.md](docs/index.md). Интерфейс: [docs/ui.md](docs/ui.md).

## Free и Pro

| Free | Pro |
|------|-----|
| Карта, список, поиск, геолокация, категории | REST API v1 + `/meta` (headless) |
| `search.php`, `return=chunks/data/json` | «Открыто сейчас», `status_hint`, amenity/brand |
| Геокод в mgr, Extension API | CSV + bulk geocode, MiniShop3 |

Pro ставится поверх Free. Без Pro REST и `working_now` недоступны. Подробнее: [docs/free-vs-pro.md](docs/free-vs-pro.md).

## Что нужно

- MODX Revolution 3.x
- PHP 8.2+
- MySQL или MariaDB
- [pdoTools](https://modx.pro/components/pdotools) (chunks на Fenom)
- [API-ключ Яндекс.Карт](https://developer.tech.yandex.ru/) — JavaScript API и HTTP Геокодер

## Установка

1. Соберите или скачайте transport: `php _build/build.php`
2. Установите пакет через **Управление пакетами**
3. Укажите ключ в `yandexmapslocator_api_key`
4. Создайте контейнер (например, «Магазины») и дочерние ресурсы-точки
5. Заполните TV (ставятся при установке) или задайте свои имена TV в настройках
6. Вставьте сниппет:

```fenom
{'!YandexMapsLocator' | snippet : [
    'parents' => 123,
    'radius' => 50,
    'sortby' => 'distance'
]}
```

## API-ключ Яндекс.Карт

Без ключа карта на фронте и серверное геокодирование не работают. Один ключ в `yandexmapslocator_api_key` используется в двух местах:

| Куда | Зачем |
|------|--------|
| Браузер (`api-maps.yandex.ru/2.1`) | Карта и маркеры |
| Сервер (`geocode-maps.yandex.ru`) | Поиск по адресу, «Моё местоположение», REST geocode, кнопка геокода в mgr |

### Как получить

1. Войдите в [Кабинет разработчика](https://developer.tech.yandex.ru/) с Яндекс ID.
2. Подключите продукт **JavaScript API и HTTP Геокодер**.
3. Ключ появится в списке ключей. Активация обычно занимает до 15 минут.
4. В MODX: **Система → Настройки системы → yandexmapslocator** → `yandexmapslocator_api_key`.

Документация: [JS API](https://yandex.ru/dev/jsapi-v2-1/doc/ru/), [Геокодер](https://yandex.ru/maps-api/products/geocoder-api). Лимиты тарифа — в [оферте](https://yandex.ru/legal/maps_api/ru/) и статистике кабинета.

### Безопасность

- Не коммитьте ключ в Git и не дублируйте его в chunks.
- В кабинете ограничьте ключ по HTTP Referer (домены сайта) и по IP для серверного геокодера.
- REST v1 не отдаёт `apiKey` в JSON. На странице сниппета ключ попадает только в URL скрипта Яндекса в браузере.

## Точки на карте

Каждая точка — опубликованный ресурс. TV по умолчанию:

| TV | Назначение |
|----|------------|
| `yandexmaps_address` | Адрес |
| `yandexmaps_latitude` | Широта |
| `yandexmaps_longitude` | Долгота |
| `yandexmaps_phone` | Телефон |
| `yandexmaps_email` | Email |
| `yandexmaps_working_hours` | Часы работы |
| `yandexmaps_category` | Категория |
| `yandexmaps_balloon_image` | Картинка в балуне |
| `yandexmaps_marker_icon` | Иконка маркера на карте |

Имена TV меняются через `yandexmapslocator_tv_*`.

## Параметры сниппета

| Параметр | Описание |
|----------|----------|
| `parents` | ID родителей через запятую |
| `limit`, `offset` | Пагинация |
| `radius` | Радиус поиска, км |
| `sortby`, `sortdir` | Сортировка (`pagetitle`, `distance`, …) |
| `tpl`, `tplOuter`, `tplSearch`, `tplEmpty`, `tplError` | Имена chunks |
| `return` | `chunks` (по умолчанию), `data`, `json` |
| `filters` | Список фильтров через запятую или JSON |
| `category` | Значение категории |
| `brand`, `amenity` / `amenities` | Pro: бренд и удобства |
| `productId` / `product_id` | Pro + MiniShop3: ID товара |
| `context` | MODX context (см. [docs/contexts.md](docs/contexts.md)) |

Полный список: [docs/snippets/yandexmapslocator.md](docs/snippets/yandexmapslocator.md).

## REST API v1 (Pro)

Headless REST только в **YandexMapsLocator Pro**: `/assets/components/yandexmapslocatorpro/api.php`.

В Free AJAX локатора — `search.php` (same-origin). `return=json` подходит для своего шаблона на том же сайте, не для CORS/headless.

Матрица: [docs/free-vs-pro.md](docs/free-vs-pro.md). API: [docs/api-v1.md](docs/api-v1.md), [docs/headless.md](docs/headless.md).

| Маршрут | Описание |
|---------|----------|
| `?route=api/v1/locations` | Список точек |
| `?route=api/v1/locations/{id}` | Деталь |
| `?route=api/v1/geocode` | Геокодирование |
| `?route=api/v1/meta` | Capabilities, поля, фильтры |

## JavaScript API

```javascript
const locator = new YandexMapsLocator('[data-yml-root]', { apiUrl, config, stores });
locator.search({ address: 'Омск, ул. Ленина, 25' });
locator.locate();
locator.on('store:click', ({ id }) => console.log(id));
```

После `locate()` кнопка «Моё местоположение» меняется на «Все точки» и сбрасывает геофильтр. На мобильном после геолокации открывается вкладка «Карта». Табы «Список» / «Карта» остаются сверху.

## Расширения (Pro и свои extras)

Регистрация через события MODX:

- `OnYandexMapsLocatorRegisterFilters`
- `OnYandexMapsLocatorRegisterFeatureProviders`
- `OnYandexMapsLocatorBeforeStorePrepare` / `AfterStorePrepare`
- `OnYandexMapsLocatorBeforeSearch` / `AfterSearch`
- `OnYandexMapsLocatorBeforeApiResponse` / `SerializeLocation`

Контракт: [docs/extension-api.md](docs/extension-api.md). Список событий: [docs/events.md](docs/events.md).

## Разработка

```bash
composer install
composer test
composer phpstan
php _build/build.php
```

## Pro

**YandexMapsLocatorPro** ставится поверх Free и не дублирует ядро. Сейчас доступны:

- REST v1 (`locations`, `geocode`), URL с `?route=api/v1/…`
- фильтр `working_now` и поле `is_open_now` (таймзона сети: `yandexmapslocator_timezone`)
- импорт/экспорт CSV в CMP
- интеграция с MiniShop3 (фильтр по товару)

Матрица версий: [docs/compatibility.md](docs/compatibility.md), [docs/free-vs-pro.md](docs/free-vs-pro.md).

## Лицензия

MIT — [core/components/yandexmapslocator/docs/license.txt](core/components/yandexmapslocator/docs/license.txt).
