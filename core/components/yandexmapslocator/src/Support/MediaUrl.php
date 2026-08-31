<?php

declare(strict_types=1);

namespace YandexMapsLocator\Support;

use MODX\Revolution\modX;

final class MediaUrl
{
    public static function resolve(modX $modx, string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        if (preg_match('#^https?://#i', $value) === 1) {
            return $value;
        }

        if (str_starts_with($value, '//')) {
            return 'https:' . $value;
        }

        $base = rtrim((string) $modx->getOption('site_url', null, ''), '/');
        if ($base === '') {
            return $value;
        }

        if (str_starts_with($value, '/')) {
            return $base . $value;
        }

        return $base . '/' . ltrim($value, '/');
    }
}
