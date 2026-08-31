<?php

declare(strict_types=1);

namespace YandexMapsLocator\Support;

use MODX\Revolution\modX;
use YandexMapsLocator\YandexMapsLocator;

final class LocatorService
{
    public static function get(modX $modx): YandexMapsLocator
    {
        if (!$modx->services->has('yandexmapslocator')) {
            throw new \RuntimeException('YandexMapsLocator service is not registered');
        }

        /** @var YandexMapsLocator $locator */
        $locator = $modx->services->get('yandexmapslocator');

        return $locator;
    }
}
