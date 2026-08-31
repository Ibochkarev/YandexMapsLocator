<?php

/**
 * Resolver: create default Template Variables for store locations
 * in the YandexMapsLocator category.
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
$categoryName = 'YandexMapsLocator';

/** @var \MODX\Revolution\modCategory|null $category */
$category = $modx->getObject('modCategory', ['category' => $categoryName]);
if (!$category) {
    /** @var \MODX\Revolution\modCategory $category */
    $category = $modx->newObject('modCategory');
    $category->fromArray([
        'category' => $categoryName,
        'parent' => 0,
    ], '', true, true);
    if (!$category->save()) {
        $modx->log(
            \MODX\Revolution\modX::LOG_LEVEL_ERROR,
            '[YandexMapsLocator] Failed to create category for TVs',
        );

        return true;
    }
}

$categoryId = (int) $category->get('id');

$tvs = [
    'yandexmaps_address' => [
        'caption' => 'Адрес',
        'description' => 'Адрес точки для геокодирования и вывода на сайте.',
        'type' => 'text',
        'rank' => 0,
    ],
    'yandexmaps_latitude' => [
        'caption' => 'Широта',
        'description' => 'Географическая широта.',
        'type' => 'text',
        'rank' => 1,
    ],
    'yandexmaps_longitude' => [
        'caption' => 'Долгота',
        'description' => 'Географическая долгота.',
        'type' => 'text',
        'rank' => 2,
    ],
    'yandexmaps_phone' => [
        'caption' => 'Телефон',
        'description' => 'Контактный телефон.',
        'type' => 'text',
        'rank' => 3,
    ],
    'yandexmaps_email' => [
        'caption' => 'Email',
        'description' => 'Контактный email.',
        'type' => 'text',
        'rank' => 4,
    ],
    'yandexmaps_working_hours' => [
        'caption' => 'Часы работы',
        'description' => 'Текстовое расписание.',
        'type' => 'textarea',
        'rank' => 5,
    ],
    'yandexmaps_category' => [
        'caption' => 'Категория',
        'description' => 'Категория для фильтра в локаторе.',
        'type' => 'text',
        'rank' => 6,
    ],
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
    if ($tv) {
        if ((int) $tv->get('category') !== $categoryId) {
            $tv->set('category', $categoryId);
            $tv->save();
        }
        continue;
    }

    /** @var \MODX\Revolution\modTemplateVar $tv */
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
    $tv->save();
}

return true;
