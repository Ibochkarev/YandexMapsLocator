<?php

if (!defined('MODX_CORE_PATH')) {
    $path = dirname(__FILE__);
    while (!file_exists($path . '/core/config/config.inc.php') && (strlen($path) > 1)) {
        $path = dirname($path);
    }
    define('MODX_CORE_PATH', $path . '/core/');
}

return [
    'name' => 'YandexMapsLocator',
    'name_lower' => 'yandexmapslocator',
    'version' => '1.0.0',
    'release' => 'pl',
    'install' => true,
    'update' => [
        'chunks' => true,
        'menus' => false,
        'permission' => false,
        'plugins' => true,
        'policies' => false,
        'policy_templates' => false,
        'resources' => false,
        'settings' => true,
        'snippets' => true,
        'templates' => false,
        'widgets' => false,
    ],
    'static' => [
        'plugins' => true,
        'snippets' => true,
        'chunks' => true,
    ],
    'log_level' => !empty($_REQUEST['download']) ? 0 : 3,
    'log_target' => php_sapi_name() === 'cli' ? 'ECHO' : 'HTML',
    'download' => !empty($_REQUEST['download']),
];
