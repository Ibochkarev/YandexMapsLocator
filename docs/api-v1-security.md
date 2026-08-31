# REST API v1 — Security

v1 не отдаёт внутренние поля MODX, не показывает unpublished ресурсы и ограничивает abuse.

## Threat model

| Угроза | Митигация |
|--------|-----------|
| Утечка internal fields/TVs | `FieldWhitelist`, `ResourceSerializer` denylist, TV whitelist (`api_resource_tvs`) |
| DoS (limit/offset) | `RequestLimits`: max limit 100, offset 10000, max 20 parents |
| Abuse geocode | Rate limit per IP + cache geocode |
| SQL injection via `where` | `where` отключён в v1 |
| IDOR unpublished | `published=1`, `deleted=0`; detail → 404 |
| Stack traces | Generic 500, log server-side |
| CORS misconfig | Explicit origins setting, default deny |
| Yandex API key leak | v1 не отдаёт `apiKey` в JSON |

## Pipeline

```
Request
  → ApiSecurityMiddleware (enabled, Bearer, rate limit, CORS preflight)
  → RequestParser (fields/include/limits, where forbidden)
  → ParentScopeValidator (optional api_allowed_parents)
  → Controller
  → LocationSerializer / ResourceSerializer
  → JsonResponse + security headers
```

## Settings

| Key | Default | Назначение |
|-----|---------|------------|
| `yandexmapslocator_api_enabled` | Yes | Kill switch |
| `yandexmapslocator_api_max_limit` | 100 | Pagination cap |
| `yandexmapslocator_api_max_offset` | 10000 | Offset cap |
| `yandexmapslocator_api_max_parents` | 20 | Max parent IDs |
| `yandexmapslocator_api_geocode_rate_limit` | 30 | Geocode req/min/IP |
| `yandexmapslocator_api_list_rate_limit` | 120 | List req/min/IP |
| `yandexmapslocator_api_cors_origins` | *(empty)* | Allowed origins |
| `yandexmapslocator_api_token` | *(empty)* | Optional Bearer |
| `yandexmapslocator_api_resource_tvs` | *(empty)* | TV whitelist for `include=tv` |
| `yandexmapslocator_api_allowed_parents` | *(empty)* | Scope list/detail |

## HTTP headers (v1)

- `Content-Type: application/json; charset=utf-8`
- `X-Content-Type-Options: nosniff`
- `Cache-Control: public, max-age=60` (list) / `no-store` (geocode)
- `Access-Control-Allow-Origin` — только если origin в allowlist
- `429` + `Retry-After: 60` при rate limit

## Context

`yandexmapslocatorpro/api.php` инициализирует MODX с параметром `context`/`ctx` (после sanitize). Допустимые значения ограничиваются `yandexmapslocator_allowed_contexts`. Context нельзя переключить произвольным query param вне allowlist.

## Headless hardening

1. Задайте `api_token` и проксируйте v1 через BFF (Next/Nuxt server route).
2. CORS: явный origin фронта, не `*`.
3. TV в API — только через `api_resource_tvs`.
4. `api_allowed_parents` — ограничьте контейнеры точек.

## Tests (CI gate)

`tests/Unit/ApiSecurityTest.php`: invalid fields/include, where rejected, serializer denylist, limits.

Перед релизом v1: `composer test` и PHPStan зелёные.
