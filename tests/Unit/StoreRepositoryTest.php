<?php

declare(strict_types=1);

use YandexMapsLocator\Filter\FilterManager;
use YandexMapsLocator\Model\SearchCriteria;
use YandexMapsLocator\Model\Store;

describe('ResourceStoreRepository criteria', function () {
    it('category filter reduces stores', function () {
        $modx = Mockery::mock(\MODX\Revolution\modX::class);
        $modx->shouldReceive('invokeEvent')->andReturn([]);

        $manager = new FilterManager($modx);
        $stores = [
            new Store(1, 'One', '', '', '/one', 'a', 55.0, 37.0, '', '', '', 'alpha', distance: 1.0),
            new Store(2, 'Two', '', '', '/two', 'b', 55.1, 37.1, '', '', '', 'beta', distance: 2.0),
        ];

        $criteria = SearchCriteria::fromArray(['category' => 'beta']);
        $filtered = $manager->apply($stores, $criteria);
        expect($filtered)->toHaveCount(1);
        expect($filtered[0]->pagetitle)->toBe('Two');
    });
});

describe('Store toArray', function () {
    it('includes distance placeholders', function () {
        $store = new Store(1, 'Shop', '', '', '/s', 'addr', 55.0, 37.0, 'phone', 'mail@test', '9-18', 'cat', distance: 2.5);
        $data = $store->toArray('km');

        expect($data['distance'])->toBe(2.5);
        expect($data['distance_km'])->toBe('2.5 км');
        expect($data['phone'])->toBe('phone');
    });
});
