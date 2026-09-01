# YandexMapsLocator — документация

Локатор точек для MODX 3 на Яндекс.Картах. Одна точка = один ресурс с TV (адрес, координаты, телефон и т.д.). На фронте: список, карта, поиск по адресу, геолокация, сортировка по расстоянию.

## Требования

- MODX Revolution 3.0+
- PHP 8.2–8.4
- MySQL / MariaDB
- [pdoTools](https://modx.pro/components/pdotools) (chunks на Fenom)
- API-ключ [Яндекс.Карт](https://developer.tech.yandex.ru/)

## Установка

### Из transport-пакета

Соберите или скачайте `.transport.zip`, установите через **Управление пакетами**. Укажите `yandexmapslocator_api_key`.

### Из исходников

```bash
composer install
php _build/build.php
```

При установке resolver создаёт TV, регистрирует события Extension API и ставит симлинки core/assets (если пакет собран из Extras).

## Быстрый старт

1. Создайте контейнер (например, «Магазины») и дочерние ресурсы-точки.
2. Заполните TV или нажмите «Получить координаты» под полем адреса на вкладке TV (plugin в менеджере).
3. Вставьте сниппет на страницу:

```fenom
{'!YandexMapsLocator' | snippet : [
    'parents' => 123,
    'radius' => 50,
    'sortby' => 'distance'
]}
```

Chunks по умолчанию: `yandexmapslocator.outer`, `.search`, `.store`, `.empty`, `.error`. Нужен pdoTools (Fenom).

## Сниппеты

| Сниппет | Назначение | Документация |
|---------|------------|--------------|
| `YandexMapsLocator` | Список + карта + поиск | [snippets/yandexmapslocator.md](snippets/yandexmapslocator.md) |

## API и фронт

| Раздел | Описание |
|--------|----------|
| [Free vs Pro](free-vs-pro.md) | Что входит в каждую версию |
| [REST API v1](api-v1.md) | Pro: `api.php` locations, geocode, meta |
| [MODX contexts](contexts.md) | Multi-context search, `&context` |
| [search.php](ui.md#разделение-free--pro) | AJAX локатора (Free) |
| [Интерфейс](ui.md) | Список, карта, балун |
| [Extension API](extension-api.md) | Контракт для Pro и extras |
| [События MODX](events.md) | Хуки поиска и Store |

## Pro

**YandexMapsLocatorPro**: REST (`?route=`, в том числе `/meta`), `working_now`, бейджи и подсказки (`status_hint`, `closes_at`), фильтры `amenity` / `brand`, CSV + bulk geocode, MiniShop3.

Часовой пояс: на точке TV `yandexmaps_timezone`, иначе сеть `yandexmapslocator_timezone`. Подробнее: [free-vs-pro.md](free-vs-pro.md), [compatibility.md](compatibility.md).

## Системные настройки

**Система → Настройки системы → yandexmapslocator**.

| Ключ | По умолчанию | Описание |
|------|--------------|----------|
| `yandexmapslocator_api_key` | *(пусто)* | Ключ JS API и Geocoder |
| `yandexmapslocator_default_zoom` | `10` | Масштаб карты |
| `yandexmapslocator_default_latitude` | `55.751244` | Центр карты, широта |
| `yandexmapslocator_default_longitude` | `37.618423` | Центр карты, долгота |
| `yandexmapslocator_cluster` | `Да` | Кластеризация маркеров |
| `yandexmapslocator_default_radius` | `50` | Радиус поиска, км |
| `yandexmapslocator_distance_unit` | `km` | Единица расстояния в выводе |
| `yandexmapslocator_default_balloon_image` | *(пусто)* | Fallback-картинка балуна |
| `yandexmapslocator_marker_icon_size` | `32,32` | Размер кастомной иконки маркера, px |
| `yandexmapslocator_default_context` | `web` | Fallback-контекст, если активный недоступен |
| `yandexmapslocator_timezone` | `Europe/Moscow` | IANA-таймзона сети (fallback), если у точки нет TV `yandexmaps_timezone`. Нужна для Pro `working_now` / `is_open_now` |
| `yandexmapslocator_allowed_contexts` | *(пусто)* | Белый список context key через запятую. Пусто — любой существующий |
| `yandexmapslocator_tv_*` | см. README | Имена TV вместо стандартных `yandexmaps_*` |

Настройки REST (`api_enabled`, `api_token`, CORS, rate limit) — в [api-v1-security.md](api-v1-security.md). Активны после установки Pro.

## Разработка

```bash
composer test
composer phpstan
composer cs-fix
```

Каркас близок к [Reactions](https://github.com/modx-pro/Reactions).
