<?php

declare(strict_types=1);

require_once __DIR__ . '/modx-bootstrap.php';

$boot = yandexmapslocatorBootstrapPublicEndpoint();
if ($boot === null) {
    yandexmapslocatorJsonBootstrapError(
        'MODX or YandexMapsLocator service not available',
        'modx_bootstrap',
    );
}

[, $locator] = $boot;

$handler = new \YandexMapsLocator\Frontend\SearchHandler($locator);
$handler->handle();
