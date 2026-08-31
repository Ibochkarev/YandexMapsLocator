<?php

declare(strict_types=1);

namespace YandexMapsLocator\Support;

use MODX\Revolution\modContext;
use MODX\Revolution\modX;
use YandexMapsLocator\Exception\InvalidContextException;

final class ContextResolver
{
    public function __construct(private readonly modX $modx)
    {
    }

    /**
     * Active MODX context key (fallback: default_context or web).
     */
    public function currentKey(): string
    {
        $key = $this->modx->context?->get('key');

        return is_string($key) && $key !== '' ? $key : $this->defaultKey();
    }

    public function defaultKey(): string
    {
        $configured = trim((string) Settings::get($this->modx, 'default_context', ''));

        return $configured !== '' ? $configured : 'web';
    }

    /**
     * @return list<string>
     */
    public function resolveKeys(?string $requested): array
    {
        $requestedKeys = $this->parseList($requested);
        if ($requestedKeys === []) {
            return [$this->currentKey()];
        }

        $resolved = [];
        foreach ($requestedKeys as $key) {
            if (!$this->isAllowed($key) || !$this->exists($key)) {
                continue;
            }
            $resolved[] = $key;
        }

        return array_values(array_unique($resolved));
    }

    /**
     * @return list<string>
     *
     * @throws InvalidContextException when requested contexts are not allowed
     */
    public function assertAllowed(?string $requested): array
    {
        $requestedKeys = $this->parseList($requested);
        if ($requestedKeys === []) {
            return [$this->currentKey()];
        }

        $keys = $this->resolveKeys($requested);
        if ($keys === []) {
            throw new InvalidContextException('invalid_context');
        }

        return $keys;
    }

    public function isAllowed(string $contextKey): bool
    {
        $allowlist = trim((string) Settings::get($this->modx, 'allowed_contexts', ''));
        if ($allowlist === '') {
            return true;
        }

        $allowed = array_values(array_filter(array_map('trim', explode(',', $allowlist))));

        return in_array($contextKey, $allowed, true);
    }

    public function exists(string $contextKey): bool
    {
        if ($contextKey === '') {
            return false;
        }

        /** @var modContext|null $context */
        $context = $this->modx->getObject(modContext::class, ['key' => $contextKey]);

        return $context instanceof modContext;
    }

    /**
     * Sanitize context key for MODX bootstrap (format only).
     */
    public static function sanitizeBootstrapKey(?string $requested): string
    {
        $requested = trim((string) $requested);
        if ($requested === '') {
            return 'web';
        }

        if (preg_match('/^[a-z0-9_-]+$/i', $requested) !== 1) {
            return 'web';
        }

        return $requested;
    }

    /**
     * @return list<string>
     */
    private function parseList(?string $requested): array
    {
        $requested = trim((string) $requested);
        if ($requested === '') {
            return [];
        }

        $parts = array_values(array_unique(array_filter(
            array_map('trim', explode(',', $requested)),
            static fn (string $part): bool => $part !== '',
        )));

        return $parts;
    }
}
