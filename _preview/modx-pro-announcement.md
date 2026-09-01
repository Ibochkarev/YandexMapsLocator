<p><strong>Анонс для сообщества MODX.pro</strong></p>

<p><strong>YandexMapsLocator</strong> 1.0.0-pl7 — локатор точек на Яндекс.Картах для <strong>MODX Revolution 3</strong> и PHP 8.2+. Одна точка = один ресурс с TV. Чанки на Fenom (pdoTools). Карта, список, поиск по адресу, геолокация, маршрут в Навигатор.</p>

<p><strong>Репозиторий:</strong> <a href="https://github.com/Ibochkarev/YandexMapsLocator" target="_blank" rel="noopener noreferrer">github.com/Ibochkarev/YandexMapsLocator</a></p>

<p><strong>Экосистема.</strong> Free закрывает витрину сети на сайте. Платное расширение <strong>YandexMapsLocatorPro</strong> (отдельный пакет) добавляет REST v1 + <code>/meta</code>, «открыто сейчас» с TZ на точке, amenity/brand, CSV + bulk geocode и фильтр точек по товару MiniShop3. Матрица: <a href="https://docs.modx.pro/components/yandexmapslocator/free-vs-pro" target="_blank" rel="noopener noreferrer">Free и Pro</a>.</p>

<p><strong>Для кого:</strong> интегратор или владелец сети на MODX 3, которому нужна карта магазинов / ПВЗ без своей таблицы локаций.</p>

<p><strong>Что внутри (для разработки)</strong></p>
<ul>
  <li>Сниппет <code>YandexMapsLocator</code>, режимы <code>return=chunks|data|json</code>, AJAX <code>search.php</code>.</li>
  <li>Extension API: события фильтров, feature providers, хуки Store/search. Pro подключается capability <code>pro</code>.</li>
  <li>BEM <code>yml-*</code>, контракт <code>data-yml-*</code>, ES-модули, CSS-токены, JS API <code>search()</code> / <code>locate()</code>.</li>
  <li>Multi-context, TV из transport, геокод в mgr, Pest / PHPStan / CI в репозитории.</li>
</ul>

<p><strong>Документация.</strong> <a href="https://docs.modx.pro/components/yandexmapslocator/" target="_blank" rel="noopener noreferrer">docs.modx.pro/components/yandexmapslocator</a> · <a href="https://docs.modx.pro/components/yandexmapslocator/quick-start" target="_blank" rel="noopener noreferrer">быстрый старт</a> · <a href="https://github.com/Ibochkarev/YandexMapsLocator" target="_blank" rel="noopener noreferrer">GitHub</a>.</p>

<cut>

<p><strong>Анонс для покупателей ModStore</strong></p>

<p><strong>YandexMapsLocator</strong> — локатор точек сети на Яндекс.Картах для MODX 3. На странице: карта с маркерами, список, поиск по адресу и «Моё местоположение». Точки — обычные ресурсы MODX с TV. Ядро сайта не патчите.</p>

<p><strong>Зачем это сайту</strong></p>
<ul>
  <li><strong>Витрина сети.</strong> Посетитель видит ближайшие точки на карте и в списке, строит маршрут в Навигатор.</li>
  <li><strong>Редактирование как контент.</strong> Адрес и координаты правятся в ресурсе. Есть кнопка геокода в менеджере.</li>
  <li><strong>Мобильный сценарий.</strong> Табы «Список» / «Карта», геолокация браузера, сброс фильтра «Все точки».</li>
  <li><strong>Свой вид.</strong> Чанки Fenom, CSS-переменные <code>--yml-*</code>, хуки JS без привязки к классам.</li>
  <li><strong>Запас на рост.</strong> Free хватает для карты на сайте. Pro докупается отдельно: REST, «открыто сейчас», amenity/brand, CSV, MiniShop3.</li>
</ul>

<p><strong>Возможности Free</strong></p>
<ol>
  <li>Сниппет <code>YandexMapsLocator</code>: карта, список, поиск, геолокация, категории, сортировка по distance.</li>
  <li>Режимы <code>chunks</code> / <code>data</code> / <code>json</code>, AJAX <code>search.php</code>.</li>
  <li>TV адреса, координат, телефона, часов, категории, balloon/marker image.</li>
  <li>Балун, маршрут, кластеризация, multi-context.</li>
  <li>Extension API для Pro и своих extras.</li>
</ol>

<p><strong>Пример</strong></p>
<pre>{'!YandexMapsLocator' | snippet : [
    'parents' => 123,
    'radius' => 50,
    'sortby' => 'distance'
]}</pre>

<p><strong>Четыре шага</strong></p>
<ol>
  <li>Установите transport через Управление пакетами. См. <a href="https://docs.modx.pro/components/yandexmapslocator/quick-start" target="_blank" rel="noopener noreferrer">быстрый старт</a>.</li>
  <li>Задайте <code>yandexmapslocator_api_key</code> (JS API и HTTP Геокодер в кабинете Яндекса). <a href="https://docs.modx.pro/components/yandexmapslocator/settings" target="_blank" rel="noopener noreferrer">Настройки</a>.</li>
  <li>Создайте контейнер и дочерние точки, заполните TV.</li>
  <li>Вызовите сниппет (нужен pdoTools). <a href="https://docs.modx.pro/components/yandexmapslocator/snippets/YandexMapsLocator" target="_blank" rel="noopener noreferrer">Параметры сниппета</a>.</li>
</ol>

<p><strong>Требования</strong></p>
<ul>
  <li>MODX ≥ 3.0, PHP ≥ 8.2, MySQL/MariaDB</li>
  <li>pdoTools (Fenom)</li>
  <li>API-ключ Яндекс.Карт</li>
</ul>

<p>Лицензия: MIT. Pro — отдельный платный пакет поверх Free.</p>

<p><strong>Ссылки</strong></p>
<ul>
  <li><a href="https://docs.modx.pro/components/yandexmapslocator/" target="_blank" rel="noopener noreferrer">Документация</a></li>
  <li><a href="https://github.com/Ibochkarev/YandexMapsLocator" target="_blank" rel="noopener noreferrer">GitHub</a></li>
  <li><a href="https://t.me/ibochkarev" target="_blank" rel="noopener noreferrer"><u>Поддержка автора</u></a></li>
</ul>

YandexMapsLocator: локатор точек на Яндекс.Картах для MODX 3
