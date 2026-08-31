<?php

declare(strict_types=1);

namespace YandexMapsLocator\Snippet;

use MODX\Revolution\modX;
use YandexMapsLocator\Support\LocatorService;
use YandexMapsLocator\YandexMapsLocator;

abstract class AbstractSnippet
{
    public function __construct(protected readonly modX $modx)
    {
    }

    protected function locator(): YandexMapsLocator
    {
        return LocatorService::get($this->modx);
    }
}
