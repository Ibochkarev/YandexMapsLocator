<?php

/** @var \MODX\Revolution\modX $modx */

$corePath = '';
$dir = __DIR__;
for ($i = 0; $i < 10; $i++) {
    $candidate = $dir . '/config.core.php';
    if (is_readable($candidate)) {
        $corePath = $candidate;
        break;
    }
    $parent = dirname($dir);
    if ($parent === $dir) {
        break;
    }
    $dir = $parent;
}

if ($corePath === '' && !empty($_SERVER['DOCUMENT_ROOT'])) {
    $docRootCandidate = rtrim((string) $_SERVER['DOCUMENT_ROOT'], '/\\') . '/config.core.php';
    if (is_readable($docRootCandidate)) {
        $corePath = $docRootCandidate;
    }
}

if ($corePath === '') {
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode([
        'success' => false,
        'message' => 'MODX config.core.php not found',
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

require_once $corePath;

require_once MODX_CORE_PATH . 'config/' . MODX_CONFIG_KEY . '.inc.php';
require_once MODX_CONNECTORS_PATH . 'index.php';

$modx->getUser('mgr', true);
$modx->lexicon->load('yandexmapslocator:default');

if (!$modx->user || !$modx->hasPermission('save_document')) {
    header('Content-Type: application/json; charset=' . $modx->getOption('modx_charset', null, 'UTF-8'));
    echo json_encode([
        'success' => false,
        'message' => $modx->lexicon('access_denied'),
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$path = MODX_CORE_PATH . 'components/yandexmapslocator/src/Processors/';
$modx->getRequest();

/** @var \MODX\Revolution\modConnectorRequest $request */
$request = $modx->request;
$request->handleRequest([
    'processors_path' => $path,
    'location' => '',
]);
