<?php

return [
    'YandexMapsLocator' => [
        'file' => 'yandexmapslocator',
        'description' => 'Cache invalidation and manager geocode UI for YandexMapsLocator.',
        'events' => [
            'OnDocFormSave' => [],
            'OnSiteRefresh' => [],
            'OnDocFormRender' => [],
        ],
    ],
];
