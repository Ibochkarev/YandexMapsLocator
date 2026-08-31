<?php

declare(strict_types=1);

namespace YandexMapsLocator\Support;

use MODX\Revolution\modX;
use YandexMapsLocator\Service\CacheService;

final class RateLimiter
{
    public function __construct(
        private readonly modX $modx,
        private readonly CacheService $cache,
    ) {
    }

    public function check(string $bucket, int $limitPerMinute): bool
    {
        if ($limitPerMinute <= 0) {
            return false;
        }

        if ($this->isExempt()) {
            return true;
        }

        $key = $this->key($bucket);
        $count = (int) $this->cache->get($key);
        if ($count >= $limitPerMinute) {
            return false;
        }

        $this->cache->set($key, $count + 1, 120);

        return true;
    }

    public function geocodeLimit(): int
    {
        $configured = (int) Settings::get($this->modx, 'api_geocode_rate_limit', 30);

        return $configured > 0 ? $configured : 30;
    }

    public function listLimit(): int
    {
        $configured = (int) Settings::get($this->modx, 'api_list_rate_limit', 120);

        return $configured > 0 ? $configured : 120;
    }

    private function key(string $bucket): string
    {
        $minute = gmdate('YmdHi');

        return 'rate/' . $bucket . '/' . md5($this->clientIp()) . '/' . $minute;
    }

    private function isExempt(): bool
    {
        if ((bool) $this->modx->getOption('debug', null, false)) {
            return true;
        }

        $ip = $this->clientIp();

        return in_array($ip, ['127.0.0.1', '::1'], true);
    }

    private function clientIp(): string
    {
        $remote = $_SERVER['REMOTE_ADDR'] ?? '';
        if (!is_string($remote) || $remote === '') {
            return '0.0.0.0';
        }

        if (!(bool) Settings::get($this->modx, 'api_trust_proxy', false)) {
            return $remote;
        }

        $forwarded = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '';
        if (!is_string($forwarded) || $forwarded === '') {
            return $remote;
        }

        $parts = array_values(array_filter(array_map('trim', explode(',', $forwarded))));
        if ($parts === []) {
            return $remote;
        }

        return $parts[0];
    }
}
