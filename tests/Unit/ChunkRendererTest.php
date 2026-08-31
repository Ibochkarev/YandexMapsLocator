<?php

declare(strict_types=1);

use YandexMapsLocator\Model\Store;
use YandexMapsLocator\Support\ChunkRenderer;

describe('ChunkRenderer', function () {
    it('returns empty string for empty chunk name', function () {
        $modx = new MODX\Revolution\modX();
        $renderer = new ChunkRenderer($modx);

        expect($renderer->render('', ['foo' => 'bar']))->toBe('');
    });

    it('renders stores via modx fallback', function () {
        $modx = new MODX\Revolution\modX();
        $modx->setChunkTemplate('yandexmapslocator.store', 'store-{$id}');
        $renderer = new ChunkRenderer($modx);

        $store = new Store(
            id: 7,
            pagetitle: 'Test',
            longtitle: '',
            description: '',
            url: '/test',
            address: 'Addr',
            latitude: 55.0,
            longitude: 73.0,
            phone: '',
            email: '',
            workingHours: '',
            category: '',
        );

        expect($renderer->renderStores([$store], 'yandexmapslocator.store'))->toBe('store-7');
    });
});

describe('Store withDistance', function () {
    it('returns new instance with rounded distance', function () {
        $store = new Store(
            id: 1,
            pagetitle: 'A',
            longtitle: '',
            description: '',
            url: '/a',
            address: '',
            latitude: 55.0,
            longitude: 73.0,
            phone: '',
            email: '',
            workingHours: '',
            category: '',
        );

        $withDistance = $store->withDistance(12.3456);

        expect($withDistance->distance)->toBe(12.346)
            ->and($withDistance->id)->toBe(1)
            ->and($store->distance)->toBeNull();
    });
});
