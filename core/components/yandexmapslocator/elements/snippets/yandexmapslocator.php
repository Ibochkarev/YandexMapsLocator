<?php

/**
 * @var \MODX\Revolution\modX $modx
 * @var array<string, mixed> $scriptProperties
 */

use YandexMapsLocator\Snippet\YandexMapsLocatorSnippet;

return (new YandexMapsLocatorSnippet($modx))->process($scriptProperties);
