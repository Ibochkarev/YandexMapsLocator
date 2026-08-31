# События MODX

| Событие | Когда срабатывает |
|---------|-------------------|
| `OnYandexMapsLocatorRegisterFilters` | Сборка реестра фильтров |
| `OnYandexMapsLocatorRegisterFeatureProviders` | Регистрация Pro и сторонних провайдеров |
| `OnYandexMapsLocatorBeforeStorePrepare` | До финализации DTO Store |
| `OnYandexMapsLocatorAfterStorePrepare` | После сборки DTO Store |
| `OnYandexMapsLocatorBeforeSearch` | До запроса в репозиторий (snippet и REST API) |
| `OnYandexMapsLocatorAfterSearch` | После загрузки списка точек |
| `OnYandexMapsLocatorSerializeLocation` | Перед отдачей полей location в REST v1 |
| `OnYandexMapsLocatorBeforeApiResponse` | Перед JSON REST v1 (`version`, `action`, `payload`) |

## Мутация Store в событиях

Плагины могут вернуть изменённый `Store` через `$modx->event->output($store)` или присвоить новый объект в `store` (параметр передаётся по ссылке в `BeforeStorePrepare` / `AfterStorePrepare`).

## REST API и BeforeSearch

`OnYandexMapsLocatorBeforeSearch` вызывается и для snippet, и для REST. После события API повторно применяет `ApiSearchGuard`: parent scope, лимиты, запрет `where`, сброс `product_id` без Pro.
