<?php

declare(strict_types=1);

namespace YandexMapsLocator\Support;

final class WorkingHoursFormatter
{
    /** @var list<string> */
    private const DAY_KEYS = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];

    /** @var array<string, string> */
    private const DAY_LABELS = [
        'mon' => 'Пн',
        'tue' => 'Вт',
        'wed' => 'Ср',
        'thu' => 'Чт',
        'fri' => 'Пт',
        'sat' => 'Сб',
        'sun' => 'Вс',
    ];

    public static function format(string $raw): string
    {
        $raw = trim($raw);
        if ($raw === '') {
            return '';
        }

        if (!str_starts_with($raw, '{') && !str_starts_with($raw, '[')) {
            return $raw;
        }

        try {
            $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return $raw;
        }

        if (!is_array($data)) {
            return $raw;
        }

        if (self::isAlwaysOpen($data)) {
            return 'Круглосуточно';
        }

        if (self::isAlwaysClosed($data)) {
            return '';
        }

        $lines = [];
        foreach (self::DAY_KEYS as $key) {
            $slots = $data[$key] ?? null;
            if (!is_array($slots) || $slots === []) {
                continue;
            }

            $normalized = array_values(array_filter(array_map(
                static fn ($slot) => is_string($slot) ? trim($slot) : '',
                $slots,
            )));

            if ($normalized === []) {
                continue;
            }

            $lines[] = (self::DAY_LABELS[$key] ?? $key) . ': ' . implode(', ', $normalized);
        }

        return $lines === [] ? $raw : implode(' · ', $lines);
    }

    public static function formatCompact(string $raw): string
    {
        $raw = trim($raw);
        if ($raw === '') {
            return '';
        }

        if (!str_starts_with($raw, '{') && !str_starts_with($raw, '[')) {
            return $raw;
        }

        try {
            $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return $raw;
        }

        if (!is_array($data)) {
            return $raw;
        }

        if (self::isAlwaysOpen($data)) {
            return 'Круглосуточно';
        }

        if (self::isAlwaysClosed($data)) {
            return '';
        }

        /** @var array<string, string> $dayHours */
        $dayHours = [];
        foreach (self::DAY_KEYS as $key) {
            $slots = $data[$key] ?? null;
            if (!is_array($slots) || $slots === []) {
                continue;
            }

            $normalized = array_values(array_filter(array_map(
                static fn ($slot) => is_string($slot) ? trim($slot) : '',
                $slots,
            )));

            if ($normalized === []) {
                continue;
            }

            $dayHours[$key] = implode(', ', $normalized);
        }

        if ($dayHours === []) {
            return $raw;
        }

        $groups = [];
        $current = null;

        foreach (self::DAY_KEYS as $key) {
            $value = $dayHours[$key] ?? null;
            if ($current === null || $current['value'] !== $value) {
                if ($current !== null) {
                    $groups[] = $current;
                }
                $current = ['start' => $key, 'end' => $key, 'value' => $value];
                continue;
            }

            $current['end'] = $key;
        }

        if ($current !== null) {
            $groups[] = $current;
        }

        $segments = [];
        foreach ($groups as $group) {
            if ($group['value'] === null) {
                continue;
            }

            $startLabel = self::DAY_LABELS[$group['start']] ?? $group['start'];
            $endLabel = self::DAY_LABELS[$group['end']] ?? $group['end'];
            $rangeLabel = $group['start'] === $group['end']
                ? $startLabel
                : $startLabel . '–' . $endLabel;

            $segments[] = $rangeLabel . ' ' . $group['value'];
        }

        return implode(' · ', $segments);
    }

    /**
     * Compact schedule with day labels wrapped for store-card emphasis (safe HTML).
     */
    public static function formatCompactHtml(string $raw): string
    {
        $plain = self::formatCompact($raw);
        if ($plain === '') {
            return '';
        }

        return self::emphasizeDayLabels($plain);
    }

    /**
     * Escape plain schedule text and wrap day/range labels in <span class="yml-store__hours-day">.
     */
    public static function emphasizeDayLabels(string $text): string
    {
        $escaped = htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $pattern = '/((?:Пн|Вт|Ср|Чт|Пт|Сб|Вс|Mon|Tue|Wed|Thu|Fri|Sat|Sun)'
            . '(?:[–-](?:Пн|Вт|Ср|Чт|Пт|Сб|Вс|Mon|Tue|Wed|Thu|Fri|Sat|Sun))?)/u';

        $result = preg_replace(
            $pattern,
            '<span class="yml-store__hours-day">$1</span>',
            $escaped,
        );

        return is_string($result) ? $result : $escaped;
    }

    /**
     * @param array<string, mixed> $data
     */
    private static function isAlwaysOpen(array $data): bool
    {
        foreach (self::DAY_KEYS as $key) {
            $slots = $data[$key] ?? [];
            if (!is_array($slots)) {
                return false;
            }

            $normalized = array_values(array_filter(array_map(
                static fn ($slot) => is_string($slot) ? trim($slot) : '',
                $slots,
            )));

            if ($normalized !== ['00:00-23:59'] && $normalized !== ['00:00-24:00']) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array<string, mixed> $data
     */
    private static function isAlwaysClosed(array $data): bool
    {
        foreach (self::DAY_KEYS as $key) {
            $slots = $data[$key] ?? [];
            if (is_array($slots) && $slots !== []) {
                return false;
            }
        }

        return true;
    }
}
