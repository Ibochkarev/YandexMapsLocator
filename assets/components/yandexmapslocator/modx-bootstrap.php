<?php

declare(strict_types=1);

/**
 * Shared MODX bootstrap for public JSON endpoints (search.php).
 *
 * @return array{0: \MODX\Revolution\modX, 1: \YandexMapsLocator\YandexMapsLocator}|null
 */
function yandexmapslocatorBootstrapPublicEndpoint(): ?array
{
    define('MODX_API_MODE', true);

    $modxIndex = yandexmapslocatorResolveModxIndex();
    if ($modxIndex === null) {
        return null;
    }

    require_once $modxIndex;

    /** @var \MODX\Revolution\modX $modx */
    $modx->getRequest();
    $modx->initialize(yandexmapslocatorSanitizeBootstrapContextKey(
        (string) ($_GET['context'] ?? $_GET['ctx'] ?? ''),
    ));

    if (!$modx->services->has('yandexmapslocator')) {
        return null;
    }

    /** @var \YandexMapsLocator\YandexMapsLocator $locator */
    $locator = $modx->services->get('yandexmapslocator');

    return [$modx, $locator];
}

/**
 * @return non-empty-string|null
 */
function yandexmapslocatorResolveModxIndex(): ?string
{
    $candidates = [];

    $docRoot = $_SERVER['DOCUMENT_ROOT'] ?? '';
    if (is_string($docRoot) && $docRoot !== '') {
        $candidates[] = rtrim(str_replace('\\', '/', $docRoot), '/') . '/index.php';
    }

    $script = $_SERVER['SCRIPT_FILENAME'] ?? '';
    if (is_string($script) && $script !== '') {
        $candidates[] = dirname(str_replace('\\', '/', $script), 3) . '/index.php';
    }

    $dir = str_replace('\\', '/', __DIR__);
    for ($i = 3; $i <= 6; ++$i) {
        $candidates[] = dirname($dir, $i) . '/index.php';
    }

    $cwd = getcwd();
    if (is_string($cwd) && $cwd !== '') {
        $walk = str_replace('\\', '/', $cwd);
        for ($i = 0; $i < 8; ++$i) {
            $candidates[] = $walk . '/index.php';
            $parent = dirname($walk);
            if ($parent === $walk) {
                break;
            }
            $walk = $parent;
        }
    }

    foreach ($candidates as $index) {
        if (!is_string($index) || $index === '') {
            continue;
        }
        $index = str_replace('\\', '/', $index);
        $root = dirname($index);
        if (is_file($index) && is_file($root . '/core/config/config.inc.php')) {
            return $index;
        }
    }

    return null;
}

function yandexmapslocatorSanitizeBootstrapContextKey(?string $requested): string
{
    $requested = trim((string) $requested);
    if ($requested === '') {
        return 'web';
    }

    if (preg_match('/^[a-z0-9_-]+$/i', $requested) !== 1) {
        return 'web';
    }

    return $requested;
}

function yandexmapslocatorJsonBootstrapError(string $error, string $code, int $status = 500): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'error' => $error,
        'code' => $code,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
