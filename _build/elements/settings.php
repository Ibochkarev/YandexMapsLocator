<?php

return [
    'api_key' => [
        'xtype' => 'textfield',
        'value' => '',
        'area' => 'yandexmapslocator_main',
    ],
    'default_zoom' => [
        'xtype' => 'numberfield',
        'value' => 10,
        'area' => 'yandexmapslocator_main',
    ],
    'default_latitude' => [
        'xtype' => 'textfield',
        'value' => '55.751244',
        'area' => 'yandexmapslocator_main',
    ],
    'default_longitude' => [
        'xtype' => 'textfield',
        'value' => '37.618423',
        'area' => 'yandexmapslocator_main',
    ],
    'cluster' => [
        'xtype' => 'combo-boolean',
        'value' => true,
        'area' => 'yandexmapslocator_main',
    ],
    'default_radius' => [
        'xtype' => 'numberfield',
        'value' => 50,
        'area' => 'yandexmapslocator_main',
    ],
    'distance_unit' => [
        'xtype' => 'list',
        'value' => 'km',
        'options' => [
            ['text' => 'km', 'value' => 'km'],
            ['text' => 'm', 'value' => 'm'],
        ],
        'area' => 'yandexmapslocator_main',
    ],
    'tv_address' => [
        'xtype' => 'textfield',
        'value' => 'yandexmaps_address',
        'area' => 'yandexmapslocator_tvs',
    ],
    'tv_latitude' => [
        'xtype' => 'textfield',
        'value' => 'yandexmaps_latitude',
        'area' => 'yandexmapslocator_tvs',
    ],
    'tv_longitude' => [
        'xtype' => 'textfield',
        'value' => 'yandexmaps_longitude',
        'area' => 'yandexmapslocator_tvs',
    ],
    'tv_phone' => [
        'xtype' => 'textfield',
        'value' => 'yandexmaps_phone',
        'area' => 'yandexmapslocator_tvs',
    ],
    'tv_email' => [
        'xtype' => 'textfield',
        'value' => 'yandexmaps_email',
        'area' => 'yandexmapslocator_tvs',
    ],
    'tv_working_hours' => [
        'xtype' => 'textfield',
        'value' => 'yandexmaps_working_hours',
        'area' => 'yandexmapslocator_tvs',
    ],
    'tv_category' => [
        'xtype' => 'textfield',
        'value' => 'yandexmaps_category',
        'area' => 'yandexmapslocator_tvs',
    ],
    'tv_balloon_image' => [
        'xtype' => 'textfield',
        'value' => 'yandexmaps_balloon_image',
        'area' => 'yandexmapslocator_tvs',
    ],
    'tv_marker_icon' => [
        'xtype' => 'textfield',
        'value' => 'yandexmaps_marker_icon',
        'area' => 'yandexmapslocator_tvs',
    ],
    'default_balloon_image' => [
        'xtype' => 'textfield',
        'value' => '',
        'area' => 'yandexmapslocator_main',
    ],
    'marker_icon_size' => [
        'xtype' => 'textfield',
        'value' => '32,32',
        'area' => 'yandexmapslocator_main',
    ],
    'default_context' => [
        'xtype' => 'textfield',
        'value' => 'web',
        'area' => 'yandexmapslocator_main',
    ],
    'timezone' => [
        'xtype' => 'textfield',
        'value' => 'Europe/Moscow',
        'area' => 'yandexmapslocator_main',
    ],
    'allowed_contexts' => [
        'xtype' => 'textfield',
        'value' => '',
        'area' => 'yandexmapslocator_main',
    ],
    'api_enabled' => [
        'xtype' => 'combo-boolean',
        'value' => true,
        'area' => 'yandexmapslocator_api',
    ],
    'api_max_limit' => [
        'xtype' => 'numberfield',
        'value' => 100,
        'area' => 'yandexmapslocator_api',
    ],
    'api_max_offset' => [
        'xtype' => 'numberfield',
        'value' => 10000,
        'area' => 'yandexmapslocator_api',
    ],
    'api_max_parents' => [
        'xtype' => 'numberfield',
        'value' => 20,
        'area' => 'yandexmapslocator_api',
    ],
    'api_geocode_rate_limit' => [
        'xtype' => 'numberfield',
        'value' => 30,
        'area' => 'yandexmapslocator_api',
    ],
    'api_list_rate_limit' => [
        'xtype' => 'numberfield',
        'value' => 120,
        'area' => 'yandexmapslocator_api',
    ],
    'api_cors_origins' => [
        'xtype' => 'textfield',
        'value' => '',
        'area' => 'yandexmapslocator_api',
    ],
    'api_token' => [
        'xtype' => 'textfield',
        'value' => '',
        'area' => 'yandexmapslocator_api',
    ],
    'api_resource_tvs' => [
        'xtype' => 'textfield',
        'value' => '',
        'area' => 'yandexmapslocator_api',
    ],
    'api_allowed_parents' => [
        'xtype' => 'textfield',
        'value' => '',
        'area' => 'yandexmapslocator_api',
    ],
    'api_trust_proxy' => [
        'xtype' => 'combo-boolean',
        'value' => false,
        'area' => 'yandexmapslocator_api',
    ],
];
