# MODX contexts

YandexMapsLocator respects MODX multi-context setups: resources are filtered by `context_key`, URLs are generated in the resource context, and JSON endpoints initialize the requested context.

## Snippet

```fenom
[[!YandexMapsLocator?
  &parents=`2080`
  &context=`en`
]]
```

| Parameter | Behavior |
|-----------|----------|
| *(empty)* | Current MODX context of the page |
| `en` | Single context |
| `en,de` | Search across multiple contexts |

Resolved context is passed to the frontend as `map_config.context` and included in `search.php` AJAX requests.

## System settings

| Setting | Purpose |
|---------|---------|
| `yandexmapslocator_default_context` | Fallback when active context is missing (default `web`) |
| `yandexmapslocator_allowed_contexts` | Allowlist for `context` query param. Empty = any existing context |

## Endpoints

`search.php` and Pro `api.php` accept `context` or `ctx`:

```
/assets/components/yandexmapslocator/search.php?parents=2080&context=en
```

Bootstrap initializes MODX with the sanitized context key before search runs. Disallowed or unknown contexts return `400 invalid_context`.

## REST v1 (Pro)

```
?route=api/v1/locations&parents=5&context=en
?route=api/v1/locations/12&context=en
```

Optional field: `context_key` in location payloads.

## Store DTO

Each store includes `context_key`. Resource URLs use `makeUrl` with the resource context.

## Events

Use `OnYandexMapsLocatorAfterStorePrepare` to adjust context-specific data per resource.
