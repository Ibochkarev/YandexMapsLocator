# Free vs Pro

| | Free | Pro |
|---|------|-----|
| Карта + список + поиск по адресу | да | да (тот же UI) |
| Геолокация, «Все точки», маршрут | да | да |
| Фильтр `category`, сортировка по distance | да | да |
| `return=chunks` / `data` / `json` | да | да |
| `search.php` (same-origin AJAX) | да | fallback, если REST выключен |
| Кнопка геокода в mgr | да | да |
| Extension API (контракт для extras) | да | использует Free |
| REST API v1 (`api.php`, CORS, Bearer, `fields`/`include`) | — | да |
| `GET /api/v1/meta` (filters, apiFields) | — | да |
| Фильтр `working_now` | — | да |
| Бейдж «Открыто» / «Закрыто» + кнопка «Только открытые» | — | да |
| Поля `is_open_now`, `status_hint`, `closes_at`, `next_open_at`, `working_hours_schedule` | — | да |
| Per-store TZ (`yandexmaps_timezone`) | — | да |
| Фильтры `amenity`, `brand` | — | да |
| CSV import/export + bulk geocode (CMP) | — | да |
| MiniShop3: карта «где забрать этот товар» | — | да (`ms3_product_ids` / `ms3_product_id` + `productId`) |

## Позиционирование

**Free** — локатор на сайте: точки как ресурсы MODX, карта, поиск, категории. Хватает для витрины сети без headless и без массового импорта.

**Pro** — REST (включая meta), «открыто сейчас» с per-store TZ, CSV и bulk geocode в CMP, MiniShop3 на карточке товара, UI-аддоны поверх Free.

`return=json` и `search.php` в Free не заменяют REST: нет CORS для чужого origin, нет `fields`/`include`, нет Bearer. Headless (Nuxt/Next) — только Pro.

## Часовой пояс (`working_now`)

Расписание в TV `yandexmaps_working_hours` — местное время точки, не UTC сервера.

1. На точке: TV `yandexmaps_timezone` (IANA), например `Europe/Moscow` или `Asia/Omsk`.
2. Fallback сети: Free-настройка `yandexmapslocator_timezone` (по умолчанию `Europe/Moscow`).

От этого зависят фильтр `working_now`, бейджи и поля `is_open_now`, `status_hint`, `closes_at`, `next_open_at`.

Для `working_now` / `is_open_now` нужен JSON в TV. Произвольный текст (в том числе «выходной») показывается в карточке, но «открыто сейчас» для него не считается: точка считается закрытой. Free `WorkingHoursFormatter` чинит double-UTF-8 в текстовых слотах после плохого импорта.

## Фильтры amenity и brand

REST и `search.php`: `amenity=wifi,card` (или `amenities`) и `brand=…`. Параметры можно передавать без явного `filters=amenity` / `filters=brand`.

На точке: TV `yandexmaps_amenities` (через запятую) и `yandexmaps_brand`. В сниппете: `amenities` / `amenity`, `brand`.

## MiniShop3 (Pro)

Free показывает всю сеть. На карточке товара MiniShop3 нужна карта только с точками, где товар доступен.

На точке: TV `ms3_product_ids` (ID через запятую или JSON-массив) или legacy `ms3_product_id`. В шаблоне товара:

```fenom
{'!YandexMapsLocator' | snippet : [
    'parents' => $storesParent,
    'productId' => $_modx->resource.id,
    'filters' => 'minishop_product',
]}
```

Параметр `product_id` / `productId` сам активирует фильтр MiniShop3 (явный `filters=minishop_product` не обязателен). Без Pro `product_id` в REST и `search.php` сбрасывается.

## CMP Pro

Меню **Компоненты → YandexMapsLocator Pro**: импорт и экспорт CSV по ID контейнера, bulk geocode для точек без координат, превью расписания на форме ресурса.

Колонки CSV (14): `id`, `pagetitle`, `address`, `latitude`, `longitude`, `phone`, `email`, `category`, `working_hours`, `timezone`, `ms3_product_id`, `ms3_product_ids`, `amenities`, `brand`.

Экспорт ищет точки в контексте `yandexmapslocator_default_context` (по умолчанию `web`), не в mgr.

### Кодировка

Экспорт: UTF-8 с BOM. Импорт из mgr: файл в base64. Импортёр снимает BOM и чинит double-UTF-8 в ячейках (`Utf8Text::fixDoubleUtf8`). После обновления пакета переимпортируйте CSV, если адреса отображались как `Ð…`.

### Mgr на форме точки

«Получить координаты» (Free) и «Проверить расписание» (Pro) стоят под полем ввода в колонке значения TV.

## REST и настройки API

Ключи `yandexmapslocator_api_*` ставит Free (общий rate limit для `search.php`). Endpoint и kill switch `api_enabled` работают после установки Pro.

Пустой `api_token` — публичный REST (удобно на локальном стенде). На production задайте Bearer token.

```
/assets/components/yandexmapslocatorpro/api.php?route=api/v1/locations
/assets/components/yandexmapslocatorpro/api.php?route=api/v1/meta
```

PATH_INFO вида `api.php/v1/...` на многих хостингах отдаёт HTML 404. Используйте query `route=`.

## Совместимость

[compatibility.md](compatibility.md)
