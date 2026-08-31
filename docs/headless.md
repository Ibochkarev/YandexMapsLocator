# Headless guide

YandexMapsLocator REST API v1 для headless-фронтов (Nuxt, Next.js, SvelteKit). Нужен установленный **YandexMapsLocator Pro** (capability `pro`).

## Базовый URL

```
https://your-site.com/assets/components/yandexmapslocatorpro/api.php
```

Маршруты через query `route`:

- `?route=api/v1/locations&parents=5`
- `?route=api/v1/locations/12`
- `?route=api/v1/geocode&address=Москва`

## Nuxt 3 server route (BFF)

```ts
// server/api/locations.get.ts
export default defineEventHandler(async (event) => {
  const query = getQuery(event);
  const base = useRuntimeConfig().locatorApiBase;
  const token = useRuntimeConfig().locatorApiToken;

  const url = new URL(base);
  url.searchParams.set('route', 'api/v1/locations');
  if (query.parents) url.searchParams.set('parents', String(query.parents));
  if (query.limit) url.searchParams.set('limit', String(query.limit));

  const headers: Record<string, string> = { Accept: 'application/json' };
  if (token) headers.Authorization = `Bearer ${token}`;

  return await $fetch(url.toString(), { headers });
});
```

`locatorApiToken` храните в server env, не в клиентском bundle.

## CORS

На MODX задайте `yandexmapslocator_api_cors_origins`:

```
https://app.example.com,https://www.example.com
```

В production не используйте `*`.

## Поля и include

```
?fields=id,title,coordinates,distance_meters&include=resource,tv
```

TV в `resource.tv` — только имена из `yandexmapslocator_api_resource_tvs`.

## Pro-поля и фильтры

- `filters=working_now` — только открытые сейчас (таймзона: `yandexmapslocator_timezone`, например `Asia/Omsk`)
- `fields=...,is_open_now` — статус «открыто сейчас»
- `fields=...,balloon_image,marker_icon` — медиа для карты

Пустой `api_token` открывает REST без Bearer. На production задайте token.

См. [api-v1.md](api-v1.md) и [api-v1-security.md](api-v1-security.md).
