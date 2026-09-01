<?php

declare(strict_types=1);

use YandexMapsLocator\Extension\FeatureProviderInterface;
use YandexMapsLocator\Extension\FeatureProviderRegistry;
use YandexMapsLocator\Extension\LocatorExtensionApi;
use YandexMapsLocator\Filter\FilterManager;
use YandexMapsLocator\Model\SearchCriteria;
use YandexMapsLocator\Model\Store;
use YandexMapsLocator\Support\DistanceFormatter;

final class TestFeatureProvider implements FeatureProviderInterface
{
    public function capabilities(): array
    {
        return ['test', 'extra'];
    }

    public function frontendModules(): array
    {
        return [['id' => 'test-module']];
    }

    public function processorActions(): array
    {
        return ['test/action'];
    }

    public function apiFields(): array
    {
        return [];
    }
}

describe('Extension API', function () {
    it('collects feature providers once per object', function () {
        $registry = new FeatureProviderRegistry();
        $registry->add(new TestFeatureProvider());
        $api = new LocatorExtensionApi($registry->all());

        expect($api->capabilities())->toContain('test')
            ->and($api->capabilities())->toContain('extra')
            ->and($api->hasCapability('test'))->toBeTrue()
            ->and($api->hasCapability('pro'))->toBeFalse()
            ->and($registry->all())->toHaveCount(1);
    });
});

describe('FilterManager category filter', function () {
    it('filters by category from criteria', function () {
        $modx = Mockery::mock(\MODX\Revolution\modX::class);
        $modx->shouldReceive('invokeEvent')->andReturn([]);

        $manager = new FilterManager($modx);
        $stores = [
            new Store(1, 'A', '', '', '/', 'a', 1.0, 1.0, '', '', '', 'food'),
            new Store(2, 'B', '', '', '/', 'b', 2.0, 2.0, '', '', '', 'shop'),
        ];

        $criteria = SearchCriteria::fromArray(['category' => 'shop']);
        $result = $manager->apply($stores, $criteria);
        expect($result)->toHaveCount(1);
        expect($result[0]->id)->toBe(2);
    });

    it('still applies category when working_now is active', function () {
        $modx = Mockery::mock(\MODX\Revolution\modX::class);
        $modx->shouldReceive('invokeEvent')->andReturn([]);

        $manager = new FilterManager($modx);
        $stores = [
            new Store(1, 'A', '', '', '/', 'a', 1.0, 1.0, '', '', '', 'food'),
            new Store(2, 'B', '', '', '/', 'b', 2.0, 2.0, '', '', '', 'shop'),
        ];

        $criteria = SearchCriteria::fromArray([
            'category' => 'shop',
            'filters' => 'working_now',
        ]);
        $result = $manager->apply($stores, $criteria);

        expect($result)->toHaveCount(1)
            ->and($result[0]->id)->toBe(2);
    });
});

describe('DistanceFormatter', function () {
    it('formats kilometers', function () {
        expect(DistanceFormatter::format(1.234, 'km'))->toBe('1.2 км');
    });

    it('formats meters', function () {
        expect(DistanceFormatter::format(1.0, 'm'))->toBe('1000 м');
    });
});

describe('SearchCriteria', function () {
    it('detects when address geocoding is required', function () {
        $criteria = SearchCriteria::fromArray([
            'parents' => '1,2',
            'address' => 'Москва',
        ]);

        expect($criteria->needsGeocoding())->toBeTrue()
            ->and($criteria->address)->toBe('Москва')
            ->and($criteria->parents)->toBe('1,2');
    });
});
