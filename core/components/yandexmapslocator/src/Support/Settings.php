<?php

declare(strict_types=1);

namespace YandexMapsLocator\Support;

use MODX\Revolution\modX;

final class Settings
{
    public static function key(string $name): string
    {
        return 'yandexmapslocator_' . $name;
    }

    public static function get(modX $modx, string $name, mixed $default = null, ?string $namespacePrefix = null): mixed
    {
        if ($namespacePrefix !== null) {
            return $modx->getOption($namespacePrefix . $name, null, $default);
        }

        return $modx->getOption(self::key($name), null, $default);
    }
}
