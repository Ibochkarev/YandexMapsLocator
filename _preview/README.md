# Материалы для публикации YandexMapsLocator

Тексты для [modstore.pro](https://modstore.pro), GitHub и MODX.pro. Папка `_preview/` в transport ZIP не попадает.

Версия: **1.0.0-pl7**. Документация: [docs.modx.pro/components/yandexmapslocator](https://docs.modx.pro/components/yandexmapslocator/). В репозитории: [README.md](../README.md), [docs/](../docs/), [changelog](../core/components/yandexmapslocator/docs/changelog.txt).

**YandexMapsLocator** (Free) — локатор точек сети на Яндекс.Картах для MODX Revolution 3. Одна точка = один ресурс с TV. Платное расширение **YandexMapsLocatorPro** (REST + `/meta`, «открыто сейчас» с TZ на точке, amenity/brand, CSV + bulk geocode, MiniShop3) ставится отдельно поверх Free. [Free и Pro](https://docs.modx.pro/components/yandexmapslocator/free-vs-pro).


## Поля карточки ModStore

| Поле | Значение |
|------|----------|
| Краткое описание (≤85 символов) | `Локатор точек на Яндекс.Картах: карта, список, поиск` (52) |
| Теги | `modx3`, `яндекс.карты`, `локатор`, `карта магазинов`, `геолокация`, `fenom`, `pdotools` |
| Категория | Работа с картами и Geo IP |
| Цена | бесплатно (MIT) |

Поле «Описание» на ModStore — HTML из **modstore-description.md** (теги ModStore: `strong`, `em`, `ul`/`ol`, `code`, `a`, `img`, `pre`, `figure` и др.).

## Структура описания

| Блок | Где | Содержание |
|------|-----|------------|
| Лид | modstore, DESCRIPTION | Что видит посетитель на сайте |
| Назначение | DESCRIPTION, modstore | Сеть магазинов / точек выдачи |
| Как работает | DESCRIPTION, modstore | Ресурс → TV → сниппет → карта |
| Возможности | все | Нумерованный список фактов Free |
| Free и Pro | все | Что в Free, что докупается в Pro |
| Сниппет и TV | DESCRIPTION, modstore | Параметры и примеры Fenom |
| Зависимости / старт | все | Ключ Яндекса, pdoTools, 4 шага |
| Скриншоты | README, modstore | Таблица кадров + заглушки PNG |

## Файлы

| Файл | Назначение |
|------|------------|
| DESCRIPTION.md | Полное описание для GitHub / карточки каталога |
| modstore-description.md | Краткое поле ≤85, теги, HTML для ModStore |
| modx-pro-announcement.md | Анонс MODX.pro: до `<cut>` — разработчики, после — покупатели |
| logo-320.png | 320×320, иконка карточки (добавьте при публикации) |
| banner-1200x630.png | 1200×630, og:image (добавьте при публикации) |
| shot-01-locator-desktop.png | Локатор desktop: список + карта |
| shot-02-locator-mobile.png | Мобильный: табы Список / Карта |
| shot-03-search-locate.png | Поиск по адресу и «Моё местоположение» |
| shot-04-store-card.png | Карточка точки: адрес, часы, маршрут |
| shot-05-mgr-geocode.png | Ресурс в mgr: TV + кнопка геокода |

PNG пока нет. Снимите по таблице ниже, положите сюда. На ModStore загрузите на file.modx.pro и подставьте URL в `src` у картинок в modstore-description.md.

## Скриншоты

Стенд QA: `https://project.test/yandexmapslocator/?yml_parents=2080` (шаблон демо `yandexmapslocator_test`, родитель точек `#2080`).

Перед съёмкой: ключ `yandexmapslocator_api_key` задан, точки опубликованы с координатами. Закройте лишние QA-блоки, если мешают кадру. В кадре — интерфейс локатора, без шапки демо-магазина, если возможно.

| Файл | Где снять | Что в кадре |
|------|-----------|-------------|
| `shot-01-locator-desktop.png` | Стенд, ширина ≥769px | Две колонки: список слева, карта справа, маркеры |
| `shot-02-locator-mobile.png` | Стенд, ширина ~390px | Табы «Список» / «Карта», одна панель |
| `shot-03-search-locate.png` | Форма поиска | Поле адреса, «Найти», «Моё местоположение» |
| `shot-04-store-card.png` | Карточка в списке | Заголовок, адрес, часы, «Показать на карте», иконка маршрута |
| `shot-05-mgr-geocode.png` | Ресурс-точка в mgr | TV адреса/координат и кнопка геокода |
| `logo-320.png` | Отдельный макет | 320×320 |
| `banner-1200x630.png` | Отдельный макет | 1200×630 |

С Pro на стенде дополнительно: бейдж «Открыто»/«Закрыто», `status_hint`, кнопка «Только открытые». Для карточки Free можно снять стенд без Pro или кадр без этих элементов.

## Сборка transport

```bash
composer install
composer test
php _build/build.php
```

Zip для выгрузки: `_build/build.php?download=1` или файл в `core/packages/`.

В `_build/config.inc.php`:

| Флаг | Локальная разработка | Релиз ModStore |
|------|----------------------|----------------|
| `install` | `true` — pack и установка в текущий MODX | `false` — только `.transport.zip` |

## Куда копировать

- ModStore: краткое описание и теги из modstore-description.md, HTML-описание целиком из того же файла
- GitHub About / README: DESCRIPTION.md или корневой README
- MODX.pro: modx-pro-announcement.md (блок `<cut>`)
- Соцсети: banner-1200x630.png, когда файл готов

Репозиторий: [github.com/Ibochkarev/YandexMapsLocator](https://github.com/Ibochkarev/YandexMapsLocator). Документация: [docs.modx.pro/components/yandexmapslocator](https://docs.modx.pro/components/yandexmapslocator/).
