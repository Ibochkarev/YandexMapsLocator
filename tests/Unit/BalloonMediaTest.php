<?php

declare(strict_types=1);

use YandexMapsLocator\Model\Store;
use YandexMapsLocator\Support\MediaUrl;

it('passes through absolute urls unchanged', function () {
    $modx = new \MODX\Revolution\modX();

    expect(MediaUrl::resolve($modx, 'https://cdn.example/logo.png'))
        ->toBe('https://cdn.example/logo.png');
    expect(MediaUrl::resolve($modx, ''))->toBe('');
});

it('exposes balloon and marker fields in store dto', function () {
    $store = new Store(
        1,
        'Shop',
        '',
        '',
        '/shop',
        'addr',
        55.0,
        37.0,
        '',
        '',
        '',
        'food',
        balloonImage: 'https://example.com/photo.jpg',
        markerIcon: 'https://example.com/pin.png',
    );

    $array = $store->toArray();

    expect($array['balloon_image'])->toBe('https://example.com/photo.jpg')
        ->and($array['marker_icon'])->toBe('https://example.com/pin.png');
});
