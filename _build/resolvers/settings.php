<?php

/**
 * Restore system setting values preserved during transport install/upgrade.
 *
 * When update['settings'] is true, vehicles with UPDATE_OBJECT still merge empty
 * defaults for some keys. This resolver re-applies non-empty values captured in
 * $transport->_preserved during the same install run.
 */

use MODX\Revolution\modSystemSetting;
use xPDO\Transport\xPDOTransport;

/** @var xPDOTransport $transport */
if (!$transport->xpdo || !($transport instanceof xPDOTransport)) {
    return true;
}

if ($options[xPDOTransport::PACKAGE_ACTION] === xPDOTransport::ACTION_UNINSTALL) {
    return true;
}

$modx = $transport->xpdo;
$namespace = 'yandexmapslocator';
$keys = [
    $namespace . '_api_key',
    $namespace . '_api_token',
    $namespace . '_api_cors_origins',
    $namespace . '_api_resource_tvs',
    $namespace . '_api_allowed_parents',
];

foreach ($transport->_preserved as $preserved) {
    if (!is_array($preserved) || !isset($preserved['object']) || !is_array($preserved['object'])) {
        continue;
    }

    $object = $preserved['object'];
    $key = (string) ($object['key'] ?? '');
    if ($key === '' || !in_array($key, $keys, true)) {
        continue;
    }

    $previousValue = (string) ($object['value'] ?? '');
    if ($previousValue === '') {
        continue;
    }

    /** @var modSystemSetting|null $setting */
    $setting = $modx->getObject(modSystemSetting::class, ['key' => $key]);
    if (!$setting) {
        continue;
    }

    $currentValue = (string) $setting->get('value');
    if ($currentValue === $previousValue) {
        continue;
    }

    if ($currentValue !== '') {
        continue;
    }

    $setting->set('value', $previousValue);
    if (!$setting->save()) {
        $modx->log(
            modX::LOG_LEVEL_ERROR,
            '[YandexMapsLocator] Failed to restore setting ' . $key . ' after package install',
        );
    }
}

return true;
