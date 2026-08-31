# Сниппет YandexMapsLocator

Выводит локатор: форма поиска, список точек, карта Яндекса. HTML собирается из chunks (Fenom), JS синхронизирует список с маркерами.

## Параметры

| Параметр | По умолчанию | Описание |
|----------|--------------|----------|
| `parents` | *(пусто)* | ID родителей через запятую |
| `limit` | `0` | Лимит (0 — без ограничения) |
| `offset` | `0` | Смещение |
| `radius` | `0` | Радиус, км (0 — из `yandexmapslocator_default_radius`) |
| `sortby` | `pagetitle` | `pagetitle`, `distance`, `menuindex`, `id`, … |
| `sortdir` | `ASC` | `ASC` или `DESC` |
| `tpl` | `yandexmapslocator.store` | Chunk одной точки |
| `tplOuter` | `yandexmapslocator.outer` | Обёртка |
| `tplSearch` | `yandexmapslocator.search` | Форма поиска |
| `tplEmpty` | `yandexmapslocator.empty` | Пустой результат |
| `tplError` | `yandexmapslocator.error` | Ошибка |
| `includeTVs` | *(пусто)* | Доп. TV в плейсхолдеры точки |
| `context` | *(текущий)* | MODX context key или список через запятую. См. [contexts.md](../contexts.md) |
| `where` | *(пусто)* | JSON-условие для ресурсов (только сниппет; в `search.php` и REST запрещён) |
| `filters` | *(пусто)* | Имена фильтров через запятую или JSON (например `category`, с Pro — `working_now`) |
| `category` | *(пусто)* | Значение категории |
| `return` | `chunks` | `chunks`, `data`, `json` |
| `latitude`, `longitude` | *(пусто)* | Стартовые координаты для сортировки/радиуса |
| `address` | *(пусто)* | Адрес для геокодирования на сервере |

## Режимы `return`

- **`chunks`** — HTML для вставки на страницу (список в исходном HTML).
- **`data`** — плейсхолдеры `yandexmapslocator.stores` (массив из `Store::toArray()`) и `yandexmapslocator.count`.
- **`json`** — JSON `{ success, results }` без обёртки chunks.

## Плейсхолдеры chunk точки (`tpl`)

Fenom-переменные из `Store::toArray()`:

| Переменная | Описание |
|------------|----------|
| `{$id}` | ID ресурса |
| `{$pagetitle}`, `{$longtitle}`, `{$description}` | Поля ресурса |
| `{$url}` | Ссылка на ресурс |
| `{$address}` | Адрес (TV) |
| `{$latitude}`, `{$longitude}` | Координаты |
| `{$phone}`, `{$email}`, `{$working_hours}` | Контакты |
| `{$working_hours_formatted}`, `{$working_hours_compact}` | Расписание (полное и компактное, plain text) |
| `{$working_hours_compact_html}` | Компактное расписание с днями в `.yml-store__hours-day` (в chunk: `{raw $working_hours_compact_html}`) |
| `{$is_open_now}` | Pro: открыто ли сейчас (нужны Pro и корректный `yandexmapslocator_timezone`) |
| `{$category}` | Категория |
| `{$balloon_image}`, `{$marker_icon}` | Медиа для балуна и маркера |
| `{$distance_formatted}` | Расстояние с единицей (если задан центр поиска) |
| `{$idx}` | Порядковый номер в выборке |

Иконка маршрута в default chunk берёт путь из `{$_modx->config['assets_url']}components/yandexmapslocator/img/yandex-navigator.svg`.

Lexicon в chunks: `{'yandexmapslocator_route' | lexicon}`.

Балун на карте дублирует ключевые поля карточки. Подробнее: [Интерфейс локатора](../ui.md).

Разметка: BEM (`yml-locator`, `yml-store`, `yml-search`), хуки через `data-yml-*`. См. [ui.md](../ui.md#data-yml-контракт).

## Примеры

### Ближайшие точки от координат

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

### Только JSON для своего фронта

```fenom
{'!YandexMapsLocator' | snippet : [
    'parents' => 42,
    'return' => 'json'
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

### Только открытые сейчас (Pro)

Задайте `yandexmapslocator_timezone` под сеть (омская: `Asia/Omsk`). Иначе «сейчас» считается в часовом поясе по умолчанию пакета (`Europe/Moscow`) или, при пустой настройке, в TZ PHP.

```fenom
{'!YandexMapsLocator' | snippet : [
    'parents' => 42,
    'filters' => 'working_now'
]}
```

## Связанные разделы

- [JSON API](../api.md)
- [События](../events.md) — изменить Store до вывода
- [Extension API](../extension-api.md) — Pro-фильтры
