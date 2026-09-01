<?php

/**
 * @var \MODX\Revolution\modX $modx
 * @var array<string, mixed> $scriptProperties
 * @var array<string, mixed> $options
 */

use YandexMapsLocator\Support\LocatorService;
use YandexMapsLocator\Support\TvNames;

switch ($modx->event->name) {
    case 'OnDocFormSave':
        if (!$modx->services->has('yandexmapslocator')) {
            break;
        }
        try {
            $locator = LocatorService::get($modx);
        } catch (\RuntimeException) {
            break;
        }
        $resource = $modx->getOption('resource', $options);
        if ($resource) {
            $tvNames = new TvNames($modx);
            $addressTv = $tvNames->get('address');
            $address = (string) $resource->getTVValue($addressTv);
            if ($address !== '') {
                $locator->getCacheService()->delete($locator->getCacheService()->geocodeKey($address));
            }
        }
        break;

    case 'OnSiteRefresh':
        if ($modx->services->has('yandexmapslocator')) {
            try {
                LocatorService::get($modx)->getCacheService()->clearPartition();
            } catch (\RuntimeException) {
                break;
            }
        }
        break;

    case 'OnDocFormRender':
        if ($modx->context->get('key') !== 'mgr') {
            break;
        }
        $assetsUrl = $modx->getOption(
            'yandexmapslocator.assets_url',
            null,
            $modx->getOption('assets_url') . 'components/yandexmapslocator/',
        );
        $connectorUrl = $assetsUrl . 'connector.php';
        $tvNames = $modx->services->has('yandexmapslocator')
            ? LocatorService::get($modx)->getTvNames()->all()
            : (new TvNames($modx))->all();

        $tvIds = [];
        foreach (['address', 'latitude', 'longitude'] as $key) {
            $tvName = $tvNames[$key] ?? '';
            if ($tvName === '') {
                continue;
            }
            $tv = $modx->getObject('modTemplateVar', ['name' => $tvName]);
            if ($tv) {
                $tvIds[$key] = (int) $tv->get('id');
            }
        }

        $configScript = '<script>window.yandexMapsLocatorMgr = ' . json_encode([
            'connectorUrl' => $connectorUrl,
            'tvNames' => $tvNames,
            'tvIds' => $tvIds,
        ], JSON_UNESCAPED_UNICODE) . ';</script>';

        $version = (string) $modx->getOption('yandexmapslocator.version', null, '1.0.0');
        $modx->regClientCSS($assetsUrl . 'css/mgr-tv-helpers.css?v=' . rawurlencode($version));
        $modx->regClientStartupHTMLBlock(
            $configScript
            . '<script src="' . $assetsUrl . 'js/mgr/geocode.js?v='
            . rawurlencode($version) . '" defer></script>',
        );
        break;
}
