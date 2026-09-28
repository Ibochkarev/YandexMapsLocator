# REST API v1 (YandexMapsLocator Pro)

Нужен установленный **YandexMapsLocator Pro** (capability `pro`). REST поставляется только в пакете Pro.

Для AJAX-поиска на странице сниппета в Free используйте `search.php` (см. [ui.md](ui.md)).

Base URL: `/assets/components/yandexmapslocatorpro/api.php`

Routing (используйте query `route=`):

- `?route=api/v1/locations` — список точек
- `?route=api/v1/locations/{id}` — деталь
- `?route=api/v1/geocode` — геокодирование
- `?route=api/v1/meta` — capabilities, поля, фильтры, endpoints

PATH_INFO (`api.php/api/v1/locations`) работает только если сервер пробрасывает PATH_INFO. На многих `.test` / shared-хостингах путь без `route=` отдаёт HTML 404.

## Аутентификация

Пустой `api_token` — публичное чтение (удобно на локальном стенде). Если токен задан, передайте заголовок:

```http
Authorization: Bearer YOUR_TOKEN
```

На production задайте `yandexmapslocator_api_token`.

## GET /api/v1/locations

Query:

| Param | Описание |
|-------|----------|
| `parents` | ID родителей через запятую (max 20) |
| `limit` | default 20, max 100 |
| `offset` | max 10000 |
| `fields` | whitelist полей через запятую |
| `include` | `resource`, `tv` (tv требует resource) |
| `sortby` | `pagetitle`, `distance`, `menuindex`, `id`, `createdon` |
| `sortdir` | `ASC` / `DESC` |
| `lat`, `lng` | координаты для сортировки по расстоянию |
| `address` | адрес (геокодируется) |
| `radius` | радиус в км |
| `filters`, `category` | фильтры локатора |
| `amenity` / `amenities` | теги удобств через запятую (можно без `filters=amenity`) |
| `brand` | фильтр по TV `yandexmaps_brand` |
| `product_id` | MiniShop3: точки с этим товаром (без Pro сбрасывается) |
| `working_now` | `1` / `true` — только открытые сейчас (нужен Pro) |
| `context` | MODX context (см. [contexts.md](contexts.md)) |

`where` запрещён в v1 (400 `where_not_allowed`).

По умолчанию в ответе короткий набор полей (`id`, `resource_id`, `title`, `address`, `coordinates`). Чтобы получить `distance`, `is_open_now`, `status_hint`, `closes_at` и т.п., перечислите их в `fields`.

Ответ:

```json
{
  "success": true,
  "data": [
    {
      "id": 12,
      "resource_id": 12,
      "title": "Магазин",
      "address": "Москва",
      "coordinates": { "lat": 55.75, "lon": 37.62 }
    }
  ],
  "meta": { "total": 42, "limit": 20, "offset": 0 }
}
```

## GET /api/v1/locations/{id}

Unpublished ресурс → **404** (не 403).

## GET /api/v1/geocode

| Param | Описание |
|-------|----------|
| `address` | строка, max 500 символов |

Rate limit: setting `yandexmapslocator_api_geocode_rate_limit` (default 30/min/IP).

## GET /api/v1/meta

Discovery endpoint для headless-клиентов: capabilities, whitelist полей, зарегистрированные фильтры, endpoints, сетевые настройки.

Пример:

```http
GET /assets/components/yandexmapslocatorpro/api.php?route=api/v1/meta
```

## Headless (Nuxt / Next)

```js
const base = 'https://example.com/assets/components/yandexmapslocatorpro/api.php';

const res = await fetch(`${base}?route=api/v1/locations&parents=5&limit=20`, {
  headers: { Accept: 'application/json' },
});
const json = await res.json();
```

CORS: задайте `yandexmapslocator_api_cors_origins` (`https://app.example.com`, не `*` в production).

## Поля location (whitelist)

Базовые: `id`, `resource_id`, `title`, `address`, `latitude`, `longitude`, `coordinates`, `phone`, `email`, `category`, `working_hours`, `working_hours_formatted`, `working_hours_compact`, `distance`, `distance_meters`, `distance_km`, `distance_formatted`, `url`, `context_key`, `balloon_image`, `marker_icon`, `resource`.

Pro (через Extension API): `is_open_now`, `status_hint`, `closes_at`, `next_open_at`, `working_hours_schedule`, `amenities`, `brand`, `timezone`.

## Kill switch

`yandexmapslocator_api_enabled` = No → 503 `api_disabled` на REST. Локатор на странице при этом использует `search.php` (`map_config.restApi = false`).

С заданным `api_token` on-page локатор тоже использует `search.php` (`map_config.restApi = false`): Bearer-токен — серверный секрет и не должен попадать в HTML. REST с токеном — для серверных клиентов (Nuxt BFF, свой backend). См. [api-v1-security.md](api-v1-security.md).
