<?php

/**
 * Resolver: register custom MODX events for YandexMapsLocator extension API.
 */

use xPDO\Transport\xPDOTransport;

/** @var xPDOTransport $transport */
if (!$transport->xpdo || !($transport instanceof xPDOTransport)) {
    return true;
}

if ($options[xPDOTransport::PACKAGE_ACTION] === xPDOTransport::ACTION_UNINSTALL) {
    return true;
}

if ($options[xPDOTransport::PACKAGE_ACTION] !== xPDOTransport::ACTION_INSTALL
    && $options[xPDOTransport::PACKAGE_ACTION] !== xPDOTransport::ACTION_UPGRADE
) {
    return true;
}

$modx = $transport->xpdo;

$events = [
    'OnYandexMapsLocatorRegisterFilters',
    'OnYandexMapsLocatorRegisterFeatureProviders',
    'OnYandexMapsLocatorBeforeStorePrepare',
    'OnYandexMapsLocatorAfterStorePrepare',
    'OnYandexMapsLocatorBeforeSearch',
    'OnYandexMapsLocatorAfterSearch',
    'OnYandexMapsLocatorBeforeApiResponse',
    'OnYandexMapsLocatorSerializeLocation',
];

foreach ($events as $name) {
    if ($modx->getObject('modEvent', ['name' => $name])) {
        continue;
    }

    /** @var \MODX\Revolution\modEvent $event */
    $event = $modx->newObject('modEvent');
    $event->fromArray([
        'name' => $name,
        'service' => 6,
        'groupname' => 'YandexMapsLocator',
    ], '', true, true);
    $event->save();
}

return true;
