<?php

/**
 * One-off helper: create balloon/marker TVs and seed resource #2081 for QA.
 * Not a transport resolver — run manually:
 *   php _build/scripts/seed-qa-media.php
 */

declare(strict_types=1);

$root = dirname(__DIR__, 4);
$core = $root . '/config.core.php';
if (!is_file($core)) {
    $core = dirname(__DIR__, 3) . '/config.core.php';
}
if (!is_file($core)) {
    fwrite(STDERR, "config.core.php not found\n");
    exit(1);
}

require $core;
require MODX_CORE_PATH . 'model/modx/modx.class.php';

$modx = new modX();
$modx->initialize('web');

$category = $modx->getObject('modCategory', ['category' => 'YandexMapsLocator']);
if (!$category) {
    fwrite(STDERR, "Category YandexMapsLocator not found\n");
    exit(1);
}

$categoryId = (int) $category->get('id');
$templateId = 50;
$resourceId = 2081;

$tvs = [
    'yandexmaps_balloon_image' => [
        'caption' => 'Изображение в балуне',
        'description' => 'Фото или логотип точки в popup карты (TV типа image).',
        'type' => 'image',
        'rank' => 7,
    ],
    'yandexmaps_marker_icon' => [
        'caption' => 'Иконка маркера',
        'description' => 'Кастомная иконка маркера на карте (TV типа image).',
        'type' => 'image',
        'rank' => 8,
    ],
];

foreach ($tvs as $name => $data) {
    /** @var \MODX\Revolution\modTemplateVar|null $tv */
    $tv = $modx->getObject('modTemplateVar', ['name' => $name]);
    if (!$tv) {
        $tv = $modx->newObject('modTemplateVar');
        $tv->fromArray([
            'name' => $name,
            'caption' => $data['caption'],
            'description' => $data['description'],
            'type' => $data['type'],
            'elements' => '',
            'default_text' => '',
            'display' => 'default',
            'display_params' => '',
            'rank' => $data['rank'],
            'locked' => 0,
            'category' => $categoryId,
        ], '', true, true);
        if (!$tv->save()) {
            fwrite(STDERR, "Failed to create TV {$name}\n");
            exit(1);
        }
        echo "Created TV {$name} (id={$tv->get('id')})\n";
    } else {
        echo "TV {$name} exists (id={$tv->get('id')})\n";
    }

    $link = $modx->getObject('modTemplateVarTemplate', [
        'tmplvarid' => $tv->get('id'),
        'templateid' => $templateId,
    ]);
    if (!$link) {
        $link = $modx->newObject('modTemplateVarTemplate');
        $link->fromArray([
            'tmplvarid' => $tv->get('id'),
            'templateid' => $templateId,
            'rank' => $data['rank'],
        ], '', true, true);
        $link->save();
        echo "Linked {$name} to template {$templateId}\n";
    }
}

$balloonPath = 'assets/components/yandexmapslocator/img/qa-balloon.png';
$markerPath = 'assets/components/yandexmapslocator/img/qa-marker.png';

$values = [
    'yandexmaps_balloon_image' => $balloonPath,
    'yandexmaps_marker_icon' => $markerPath,
];

/** @var \MODX\Revolution\modResource|null $resource */
$resource = $modx->getObject('modResource', $resourceId);
if (!$resource) {
    fwrite(STDERR, "Resource {$resourceId} not found\n");
    exit(1);
}

foreach ($values as $tvName => $value) {
    $tv = $modx->getObject('modTemplateVar', ['name' => $tvName]);
    if (!$tv) {
        continue;
    }

    $resource->setTVValue($tvName, $value);
    echo "Set {$tvName} on resource {$resourceId} => {$value}\n";
}

$modx->getCacheManager()->refresh();
echo "Done. Open https://project.test/yandexmapslocator/#yml-balloon and show store {$resourceId}.\n";
