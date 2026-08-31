<?php

declare(strict_types=1);

namespace YandexMapsLocator\Frontend;

use YandexMapsLocator\Support\JsonResponse;
use YandexMapsLocator\Support\RateLimiter;
use YandexMapsLocator\YandexMapsLocator;

/**
 * Rate limits and headers for the Free search.php endpoint (same-origin only).
 */
final class SearchSecurity
{
    private RateLimiter $rateLimiter;

    public function __construct(private readonly YandexMapsLocator $locator)
    {
        $this->rateLimiter = new RateLimiter($locator->modx, $locator->getCacheService());
    }

    public function handlePreflight(): void
    {
        if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'OPTIONS') {
            return;
        }

        http_response_code(204);
        exit;
    }

    public function assertRateLimit(string $bucket): void
    {
        $limit = match ($bucket) {
            'geocode' => $this->rateLimiter->geocodeLimit(),
            default => $this->rateLimiter->listLimit(),
        };

        if (!$this->rateLimiter->check($bucket, $limit)) {
            header('Retry-After: 60');
            JsonResponse::error('Rate limit exceeded', 'rate_limit_exceeded', 429);
        }
    }

    public function applyResponseHeaders(): void
    {
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: private, max-age=60');
    }

    public function applyErrorHeaders(): void
    {
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: no-store');
    }
}
