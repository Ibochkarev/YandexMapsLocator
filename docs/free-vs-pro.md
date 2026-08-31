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
| Фильтр `working_now` | — | да |
| Бейдж «Открыто» / «Закрыто» + кнопка «Только открытые» | — | да |
| Поля `is_open_now`, `working_hours_schedule` | — | да |
| CSV import/export (CMP в менеджере) | — | да |
| MiniShop3: карта «где забрать этот товар» | — | да (TV `ms3_product_id` + `productId`) |

## Позиционирование

**Free** — локатор на сайте: точки как ресурсы MODX, карта, поиск, категории. Хватает для витрины сети без headless и без массового импорта.

**Pro** — REST, «открыто сейчас», CSV в CMP, MiniShop3 на карточке товара, UI-аддоны поверх Free.

`return=json` и `search.php` в Free не заменяют REST: нет CORS для чужого origin, нет `fields`/`include`, нет Bearer. Headless (Nuxt/Next) — только Pro.

## Часовой пояс (`working_now`)

Расписание в TV `yandexmaps_working_hours` — местное время сети, не UTC сервера.

Настройка Free: `yandexmapslocator_timezone` (IANA). По умолчанию в пакете `Europe/Moscow`. Для омской сети укажите `Asia/Omsk`.

От этой настройки зависят фильтр `working_now`, бейджи «Открыто»/«Закрыто» и поле `is_open_now`.

Для `working_now` / `is_open_now` в TV нужен JSON-расписание. Произвольный текст показывается в карточке, но «открыто сейчас» для него не считается (точка считается закрытой).

## MiniShop3 (Pro)

Free показывает всю сеть. На карточке товара MiniShop3 нужна карта только с точками, где товар доступен (самовывоз / наличие).

На точке TV `ms3_product_id` = ID ресурса товара. В шаблоне товара:

```fenom
{'!YandexMapsLocator' | snippet : [
    'parents' => $storesParent,
    'productId' => $_modx->resource.id,
    'filters' => 'minishop_product',
]}
```

Без Pro параметр `product_id` в REST и `search.php` сбрасывается.

## CMP Pro

Меню **Компоненты → YandexMapsLocator Pro**: импорт и экспорт CSV по ID контейнера.

Колонки: `id`, `pagetitle`, `address`, `latitude`, `longitude`, `phone`, `email`, `category`, `working_hours`.

Экспорт ищет точки в контексте `yandexmapslocator_default_context` (по умолчанию `web`), не в контексте менеджера.

## REST и настройки API

Ключи `yandexmapslocator_api_*` ставит Free (общий rate limit для `search.php`). Endpoint и kill switch `api_enabled` работают после установки Pro.

Пустой `api_token` — публичный REST (удобно на локальном стенде). На production задайте Bearer token.

Рабочий URL:

```
/assets/components/yandexmapslocatorpro/api.php?route=api/v1/locations
```

PATH_INFO вида `api.php/v1/...` на многих хостингах отдаёт HTML 404. Используйте query `route=`.

## Совместимость

[compatibility.md](compatibility.md)
