## Краткое описание (ModStore, до 85 символов)

Локатор точек на Яндекс.Картах: карта, список, поиск

<!-- 52 символа -->

## Описание для карточки (абзац)

Локатор точек сети на Яндекс.Картах для MODX 3: карта, список, поиск по адресу и геолокация. Одна точка = один ресурс с TV.

## Теги для фильтрации

```
modx3, яндекс.карты, локатор, карта магазинов, геолокация, fenom, pdotools
```

Категория на ModStore: Работа с картами и Geo IP.

---

<!--
  Скриншоты: после загрузки на file.modx.pro замените src у img.
  Где снимать: _preview/README.md, раздел «Скриншоты».
-->

<p><strong>YandexMapsLocator</strong></p>

<p>Локатор точек сети на Яндекс.Картах для MODX Revolution 3. На странице: карта с маркерами, список точек, поиск по адресу и кнопка «Моё местоположение». Одна точка = один опубликованный ресурс с TV (адрес, координаты, телефон, часы). Документация: <a href="https://docs.modx.pro/components/yandexmapslocator/" target="_blank" rel="noopener">docs.modx.pro/components/yandexmapslocator</a>. Репозиторий: <a href="https://github.com/Ibochkarev/YandexMapsLocator" target="_blank" rel="noopener">github.com/Ibochkarev/YandexMapsLocator</a>.</p>

<p><strong>Кому подойдёт:</strong> сайт сети магазинов, пунктов выдачи или филиалов на MODX 3, где нужна карта на витрине без отдельной БД магазинов.</p>

<p><strong>Назначение</strong></p>
<ul>
  <li>карта Яндекса со списком точек рядом (на телефоне — табы «Список» / «Карта»);</li>
  <li>поиск по адресу и сортировка по расстоянию;</li>
  <li>геолокация браузера и сброс фильтра («Все точки»);</li>
  <li>маршрут в Яндекс Навигатор с карточки и из балуна;</li>
  <li>категории, свои иконки маркеров и картинки в балуне.</li>
</ul>

<p><strong>Как это работает</strong></p>
<ol>
  <li>Создаёте контейнер и дочерние ресурсы-точки, заполняете TV адреса и координат.</li>
  <li>В форме ресурса можно нажать кнопку геокода (нужен API-ключ Яндекса).</li>
  <li>На странице вызываете сниппет <code>YandexMapsLocator</code> с <code>parents</code> = ID контейнера.</li>
  <li>Чанки на Fenom (pdoTools) отдают HTML. JS синхронизирует список и маркеры. AJAX — <code>search.php</code> на том же сайте.</li>
</ol>

<p><strong>Возможности Free</strong></p>
<ol>
  <li><strong>Локатор.</strong> Сниппет <a href="https://docs.modx.pro/components/yandexmapslocator/snippets/YandexMapsLocator" target="_blank" rel="noopener">YandexMapsLocator</a>: форма, список, карта. Параметры <code>parents</code>, <code>limit</code>, <code>radius</code>, <code>sortby</code>, <code>category</code>, <code>filters</code>, <code>context</code>, <code>return</code>.</li>
  <li><strong>Режимы вывода.</strong> <code>chunks</code> (HTML), <code>data</code> (плейсхолдеры), <code>json</code> для своего шаблона на том же origin.</li>
  <li><strong>Геолокация и поиск.</strong> «Моё местоположение», поиск по адресу через HTTP Геокодер Яндекса, радиус и сортировка по distance.</li>
  <li><strong>Карточка и балун.</strong> Адрес, телефон, часы, маршрут, balloon image, custom marker icon.</li>
  <li><strong>TV и геокод в mgr.</strong> Набор TV ставится при установке. Имена задаёте через <code>yandexmapslocator_tv_*</code>.</li>
  <li><strong>UI.</strong> Mobile-first, BEM <code>yml-*</code>, атрибуты <code>data-yml-*</code>, CSS-переменные на <code>.yml-locator</code>.</li>
  <li><strong>Multi-context.</strong> Параметр <code>context</code>, настройки <code>default_context</code> и <code>allowed_contexts</code>.</li>
  <li><strong>Extension API.</strong> События для фильтров и feature providers. Pro и свои extras подключаются без правок ядра Free.</li>
</ol>

<p><strong>Free и Pro</strong></p>
<p>Этот пакет — <strong>Free</strong>: локатор на сайте (карта, список, поиск, геолокация, категории, Extension API). Точки — ресурсы MODX с TV. Для витрины сети этого обычно достаточно.</p>
<p><strong>YandexMapsLocatorPro</strong> — отдельный платный пакет. Ставится <em>поверх</em> Free (без Free не установится). Тот же UI локатора, плюс операции и интеграции:</p>
<ul>
  <li><strong>REST API v1</strong> — <code>api.php?route=api/v1/locations</code> (список, деталь, geocode) и <code>?route=api/v1/meta</code>. CORS, Bearer (<code>api_token</code>), whitelist <code>fields</code>/<code>include</code>, kill switch <code>api_enabled</code>. Нужен для headless (Nuxt/Next) с другого origin. См. <a href="https://docs.modx.pro/components/yandexmapslocator/pro/api" target="_blank" rel="noopener">Pro API</a>.</li>
  <li><strong>«Открыто сейчас»</strong> — фильтр <code>working_now</code>, поля <code>is_open_now</code>, <code>status_hint</code>, <code>closes_at</code>, <code>next_open_at</code>, <code>working_hours_schedule</code>, бейджи «Открыто» / «Закрыто», кнопка «Только открытые». TZ на точке: TV <code>yandexmaps_timezone</code>; иначе Free-настройка <code>yandexmapslocator_timezone</code> (IANA, например <code>Asia/Omsk</code>). В TV часов для фильтра нужен JSON. Подробнее: <a href="https://docs.modx.pro/components/yandexmapslocator/pro/working-now" target="_blank" rel="noopener">открыто сейчас</a>.</li>
  <li><strong>Фильтры amenity / brand</strong> — TV <code>yandexmaps_amenities</code>, <code>yandexmaps_brand</code>; параметры <code>amenity</code> / <code>brand</code> в сниппете, <code>search.php</code> и REST.</li>
  <li><strong>CSV в CMP</strong> — Компоненты → YandexMapsLocator Pro: импорт и экспорт по ID контейнера (14 колонок, в т.ч. timezone, amenities, brand, <code>ms3_product_ids</code>), bulk geocode для точек без координат. <a href="https://docs.modx.pro/components/yandexmapslocator/pro/manager" target="_blank" rel="noopener">Менеджер Pro</a>.</li>
  <li><strong>MiniShop3</strong> — на карточке товара карта только с точками, где товар доступен: TV <code>ms3_product_ids</code> (или legacy <code>ms3_product_id</code>) + параметр <code>productId</code>. Явный <code>filters=minishop_product</code> не обязателен. <a href="https://docs.modx.pro/components/yandexmapslocator/pro/minishop3" target="_blank" rel="noopener">MiniShop3</a>.</li>
</ul>
<p><strong>Чего нет в Free вместо REST.</strong> Режим <code>return=json</code> и endpoint <code>search.php</code> работают только same-origin: нет CORS для чужого домена, нет выбора полей <code>fields</code>/<code>include</code>, нет Bearer. Публичный read API для SPA на другом хосте — только Pro. Пустой <code>api_token</code> на стенде открывает REST без токена. На production задайте Bearer.</p>
<p>Если Pro установлен, а <code>api_enabled</code> выключен, REST отвечает 503, а локатор на странице остаётся на <code>search.php</code>.</p>
<p>Полная матрица: <a href="https://docs.modx.pro/components/yandexmapslocator/free-vs-pro" target="_blank" rel="noopener">docs.modx.pro — Free и Pro</a>.</p>

<p><strong>Пример вызова</strong></p>
<pre>{'!YandexMapsLocator' | snippet : [
    'parents' => 123,
    'radius' => 50,
    'sortby' => 'distance'
]}</pre>

<p><strong>Фильтр по категории</strong></p>
<pre>{'!YandexMapsLocator' | snippet : [
    'parents' => 42,
    'category' => 'аптека',
    'filters' => 'category'
]}</pre>

<p><strong>Только открытые сейчас (Pro)</strong></p>
<pre>{'!YandexMapsLocator' | snippet : [
    'parents' => 42,
    'filters' => 'working_now'
]}</pre>

<p>На точке можно задать TV <code>yandexmaps_timezone</code> (IANA). Иначе сеть: <code>yandexmapslocator_timezone</code>. Для фильтра в TV часов нужен JSON. Текст без JSON показывается в карточке, но «открыто» для него не считается.</p>

<p><strong>TV по умолчанию</strong></p>
<p>Ставятся при установке пакета. Имена можно сменить через настройки <code>yandexmapslocator_tv_*</code>.</p>
<ul>
  <li><code>yandexmaps_address</code> — адрес точки (поиск, балун, карточка). По нему же работает кнопка геокода в mgr.</li>
  <li><code>yandexmaps_latitude</code> / <code>yandexmaps_longitude</code> — координаты маркера на карте и сортировки по distance.</li>
  <li><code>yandexmaps_phone</code> — телефон в карточке и балуне (ссылка <code>tel:</code>).</li>
  <li><code>yandexmaps_email</code> — email точки (в данных Store / REST при запросе поля).</li>
  <li><code>yandexmaps_working_hours</code> — часы работы: текст для показа или JSON для Pro-фильтра «открыто сейчас» и бейджей.</li>
  <li><code>yandexmaps_category</code> — категория точки. Фильтр сниппета: <code>filters=category</code> + параметр <code>category</code>.</li>
  <li><code>yandexmaps_balloon_image</code> — картинка в popup балуна на карте (если пусто — fallback из настройки <code>default_balloon_image</code>).</li>
  <li><code>yandexmaps_marker_icon</code> — своя иконка маркера вместо стандартной. Размер: <code>yandexmapslocator_marker_icon_size</code> (например <code>32,32</code>).</li>
</ul>
<p>TV Pro (ставит пакет Pro): <code>yandexmaps_timezone</code>, <code>yandexmaps_amenities</code>, <code>yandexmaps_brand</code>, <code>ms3_product_ids</code> / <code>ms3_product_id</code>.</p>

<p><strong>Скриншоты</strong></p>
<p>Заглушки: снимите PNG, залейте на file.modx.pro, подставьте URL в <code>src</code>. Где снимать: таблица в <code>_preview/README.md</code>.</p>

<figure>
  <img alt="Локатор desktop: список и карта" src="shot-01-locator-desktop.png" width="800" height="450" style="max-width:100%;background:#f0f0f0;border:1px dashed #bbb;min-height:200px" />
  <figcaption>Снять: стенд локатора, ширина ≥769px. Кадр: список слева, карта справа, маркеры.</figcaption>
</figure>

<figure>
  <img alt="Локатор mobile: табы" src="shot-02-locator-mobile.png" width="800" height="450" style="max-width:100%;background:#f0f0f0;border:1px dashed #bbb;min-height:200px" />
  <figcaption>Снять: тот же стенд, viewport ~390px. Кадр: табы «Список» / «Карта».</figcaption>
</figure>

<figure>
  <img alt="Поиск и геолокация" src="shot-03-search-locate.png" width="800" height="450" style="max-width:100%;background:#f0f0f0;border:1px dashed #bbb;min-height:200px" />
  <figcaption>Снять: форма поиска. Кадр: адрес, «Найти», «Моё местоположение».</figcaption>
</figure>

<figure>
  <img alt="Карточка точки" src="shot-04-store-card.png" width="800" height="450" style="max-width:100%;background:#f0f0f0;border:1px dashed #bbb;min-height:200px" />
  <figcaption>Снять: карточка в списке. Кадр: адрес, часы, «Показать на карте», маршрут.</figcaption>
</figure>

<figure>
  <img alt="Геокод в менеджере" src="shot-05-mgr-geocode.png" width="800" height="450" style="max-width:100%;background:#f0f0f0;border:1px dashed #bbb;min-height:200px" />
  <figcaption>Снять: ресурс-точка в mgr. Кадр: TV и кнопка геокода.</figcaption>
</figure>

<p><strong>Четыре шага</strong></p>
<ol>
  <li>Установите пакет через Управление пакетами. См. <a href="https://docs.modx.pro/components/yandexmapslocator/quick-start" target="_blank" rel="noopener">быстрый старт</a>.</li>
  <li>Укажите <code>yandexmapslocator_api_key</code> (JS API и HTTP Геокодер в <a href="https://developer.tech.yandex.ru/" target="_blank" rel="noopener">кабинете Яндекса</a>). Список ключей: <a href="https://docs.modx.pro/components/yandexmapslocator/settings" target="_blank" rel="noopener">настройки</a>.</li>
  <li>Создайте контейнер и дочерние точки, заполните TV.</li>
  <li>Вызовите сниппет на странице (нужен pdoTools). Параметры: <a href="https://docs.modx.pro/components/yandexmapslocator/snippets/YandexMapsLocator" target="_blank" rel="noopener">сниппет YandexMapsLocator</a>.</li>
</ol>

<p><strong>Требования:</strong> MODX Revolution <strong>3.x</strong>, PHP <strong>8.2+</strong>, MySQL/MariaDB, <strong>pdoTools</strong>, ключ Яндекс.Карт.</p>
<p>Лицензия: MIT.</p>
