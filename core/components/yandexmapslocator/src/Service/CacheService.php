<?php

declare(strict_types=1);

namespace YandexMapsLocator\Service;

use MODX\Revolution\modX;

class CacheService
{
    private const PARTITION = 'yandexmapslocator';

    /** xPDO cacheManager partition option (xPDO::OPT_CACHE_KEY). */
    private const CACHE_KEY_OPTION = 'cache_key';

    public function __construct(private readonly modX $modx)
    {
    }

    public function get(string $key): mixed
    {
        return $this->modx->cacheManager->get($key, $this->options());
    }

    public function set(string $key, mixed $value, int $ttl = 86400): bool
    {
        return $this->modx->cacheManager->set($key, $value, $ttl, $this->options());
    }

    public function delete(string $key): bool
    {
        return $this->modx->cacheManager->delete($key, $this->options());
    }

    public function clearPartition(): bool
    {
        return (bool) $this->modx->cacheManager->clean($this->options());
    }

    public function geocodeKey(string $address): string
    {
        return 'geocode/' . md5(mb_strtolower(trim($address)));
    }

    /**
     * @return array<string, string>
     */
    private function options(): array
    {
        return [self::CACHE_KEY_OPTION => self::PARTITION];
    }
}
