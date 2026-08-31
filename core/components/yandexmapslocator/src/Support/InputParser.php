<?php

declare(strict_types=1);

namespace YandexMapsLocator\Support;

final class InputParser
{
    /**
     * @return list<int>
     */
    public static function ids(string $value): array
    {
        if ($value === '') {
            return [];
        }

        return array_values(array_filter(array_map('intval', preg_split('/\s*,\s*/', $value) ?: [])));
    }

    /**
     * @return list<string>
     */
    public static function list(string $value): array
    {
        if ($value === '') {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $value))));
    }

    /**
     * @return array<string, mixed>
     */
    public static function jsonArray(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (!is_string($value) || trim($value) === '') {
            return [];
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Names of enabled filters (comma-separated or JSON array of strings).
     *
     * @return list<string>
     */
    public static function filterNames(mixed $filters): array
    {
        if (is_array($filters)) {
            return self::stringList($filters);
        }

        if (!is_string($filters) || trim($filters) === '') {
            return [];
        }

        $decoded = json_decode($filters, true);
        if (is_array($decoded)) {
            return self::stringList($decoded);
        }

        return array_values(array_filter(array_map('trim', explode(',', $filters))));
    }

    /**
     * @param list<mixed> $values
     * @return list<string>
     */
    private static function stringList(array $values): array
    {
        $names = [];
        foreach ($values as $value) {
            if (is_string($value) && $value !== '') {
                $names[] = $value;
            }
        }

        return $names;
    }
}
