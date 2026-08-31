<?php

declare(strict_types=1);

use YandexMapsLocator\Exception\GeocoderException;
use YandexMapsLocator\Geo\Coordinate;
use YandexMapsLocator\Geo\YandexGeocoder;
use YandexMapsLocator\Service\CacheService;

describe('YandexGeocoder', function () {
    it('throws on empty address', function () {
        $modx = Mockery::mock(\MODX\Revolution\modX::class);
        $cache = Mockery::mock(CacheService::class);
        $geocoder = new YandexGeocoder($modx, $cache);

        expect(fn () => $geocoder->geocode('   '))
            ->toThrow(GeocoderException::class);
    });

    it('returns cached coordinate', function () {
        $modx = Mockery::mock(\MODX\Revolution\modX::class);
        $cache = Mockery::mock(CacheService::class);
        $cache->shouldReceive('geocodeKey')->andReturn('geocode/test');
        $cache->shouldReceive('get')->andReturn(['latitude' => 55.75, 'longitude' => 37.62]);

        $geocoder = new YandexGeocoder($modx, $cache);
        $result = $geocoder->geocode('Moscow');

        expect($result)->toBeInstanceOf(Coordinate::class);
        expect($result->latitude)->toBe(55.75);
    });

    it('throws when api key missing', function () {
        $modx = Mockery::mock(\MODX\Revolution\modX::class);
        $modx->shouldIgnoreMissing();
        $modx->shouldReceive('getOption')->andReturn('');
        $modx->shouldReceive('log');
        $cache = Mockery::mock(CacheService::class);
        $cache->shouldReceive('geocodeKey')->andReturn('geocode/test');
        $cache->shouldReceive('get')->andReturn(null);

        $geocoder = new YandexGeocoder($modx, $cache);

        expect(fn () => $geocoder->geocode('Moscow'))
            ->toThrow(GeocoderException::class);
    });
});
