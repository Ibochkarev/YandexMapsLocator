# Extension API

Версия контракта: **1** (`LocatorExtensionApi::CONTRACT_VERSION`).

## Feature providers

Реализуйте `YandexMapsLocator\Extension\FeatureProviderInterface` и зарегистрируйте провайдер в событии:

```php
// В plugin Pro или своего extra
$registry = $modx->event->params['registry'] ?? null;
if ($registry instanceof \YandexMapsLocator\Extension\FeatureProviderRegistry) {
    $registry->add(new ProFeatureProvider());
}
```

Событие: `OnYandexMapsLocatorRegisterFeatureProviders`.

Методы провайдера:

- `capabilities()` — теги возможностей (`pro` включает REST API v1 через пакет Pro, …)
- `frontendModules()` — ES-модули для `locator.js`. `src` должен быть абсолютным URL или путём от корня сайта (`/assets/.../module.js`). Относительные пути склеиваются с Free `assetsUrl` и ломают чужие пакеты. Если модуль экспортирует `install(locator)`, Free вызовет его после загрузки.
- `apiFields()` — дополнительные поля REST v1 (`?fields=`)

## Фильтры

Реализуйте `YandexMapsLocator\Filter\FilterInterface` и добавьте класс в реестр на `OnYandexMapsLocatorRegisterFilters`:

```php
$event['registry']->register(new WorkingNowFilter());
```

## Расширение JSON-ответа REST v1

На `OnYandexMapsLocatorBeforeApiResponse` можно изменить `payload` перед отдачей:

```php
// params: version, action, payload (by ref)
```

Дополнительные поля location — на `OnYandexMapsLocatorSerializeLocation` (`data` by ref).

## Правила для Pro и сторонних extras

1. Не копируйте классы Core в свой пакет.
2. Если в Free не хватает точки расширения, добавьте её в Free и выпустите новую версию.
3. В transport Pro укажите `requires: yandexmapslocator >= X < Y`.
