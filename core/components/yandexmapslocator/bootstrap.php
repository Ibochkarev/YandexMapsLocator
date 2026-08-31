<?php

/**
 * @var \MODX\Revolution\modX $modx
 * @var array $namespace
 */

$componentPath = $namespace['path'];
$vendorPath = $componentPath . 'vendor/';

if (file_exists($vendorPath . 'autoload.php')) {
    require_once $vendorPath . 'autoload.php';
}

$modx->services->add('yandexmapslocator', function ($c) use ($modx) {
    return new \YandexMapsLocator\YandexMapsLocator($modx);
});

$modx->services->add(\YandexMapsLocator\Extension\LocatorExtensionApi::class, function ($c) use ($modx) {
    /** @var \YandexMapsLocator\YandexMapsLocator $locator */
    $locator = $modx->services->get('yandexmapslocator');

    return $locator->extensionApi();
});
