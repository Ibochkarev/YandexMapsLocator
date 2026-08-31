<?php

return [
    'YandexMapsLocator' => [
        'file' => 'yandexmapslocator',
        'description' => 'Локатор точек на Яндекс.Картах: поиск, список, карта, геолокация.',
        'properties' => [
            'parents' => [
                'type' => 'textfield',
                'value' => '',
            ],
            'limit' => [
                'type' => 'numberfield',
                'value' => 0,
            ],
            'offset' => [
                'type' => 'numberfield',
                'value' => 0,
            ],
            'radius' => [
                'type' => 'numberfield',
                'value' => 0,
            ],
            'sortby' => [
                'type' => 'textfield',
                'value' => 'pagetitle',
            ],
            'sortdir' => [
                'type' => 'list',
                'options' => [
                    ['text' => 'ASC', 'value' => 'ASC'],
                    ['text' => 'DESC', 'value' => 'DESC'],
                ],
                'value' => 'ASC',
            ],
            'tpl' => [
                'type' => 'textfield',
                'value' => 'yandexmapslocator.store',
            ],
            'tplOuter' => [
                'type' => 'textfield',
                'value' => 'yandexmapslocator.outer',
            ],
            'tplSearch' => [
                'type' => 'textfield',
                'value' => 'yandexmapslocator.search',
            ],
            'tplEmpty' => [
                'type' => 'textfield',
                'value' => 'yandexmapslocator.empty',
            ],
            'tplError' => [
                'type' => 'textfield',
                'value' => 'yandexmapslocator.error',
            ],
            'includeTVs' => [
                'type' => 'textfield',
                'value' => '',
            ],
            'where' => [
                'type' => 'textfield',
                'value' => '',
            ],
            'filters' => [
                'type' => 'textfield',
                'value' => '',
            ],
            'category' => [
                'type' => 'textfield',
                'value' => '',
            ],
            'return' => [
                'type' => 'list',
                'options' => [
                    ['text' => 'chunks', 'value' => 'chunks'],
                    ['text' => 'data', 'value' => 'data'],
                    ['text' => 'json', 'value' => 'json'],
                ],
                'value' => 'chunks',
            ],
            'latitude' => [
                'type' => 'textfield',
                'value' => '',
            ],
            'longitude' => [
                'type' => 'textfield',
                'value' => '',
            ],
            'address' => [
                'type' => 'textfield',
                'value' => '',
            ],
            'context' => [
                'type' => 'textfield',
                'value' => '',
                'description' => 'MODX context key or comma-separated list (empty = current context).',
            ],
        ],
    ],
];
