<?php

declare(strict_types=1);

use YandexMapsLocator\Geo\Coordinate;
use YandexMapsLocator\Geo\HaversineDistanceCalculator;

describe('HaversineDistanceCalculator', function () {
    it('returns zero for identical coordinates', function () {
        $calc = new HaversineDistanceCalculator();
        expect($calc->calculate(55.75, 37.62, 55.75, 37.62))->toBe(0.0);
    });

    it('calculates distance between nearby points', function () {
        $calc = new HaversineDistanceCalculator();
        $distance = $calc->calculate(55.751244, 37.618423, 55.755819, 37.617644);
        expect($distance)->toBeGreaterThan(0.0);
        expect($distance)->toBeLessThan(2.0);
    });
});

describe('Coordinate', function () {
    it('validates coordinates', function () {
        expect((new Coordinate(55.75, 37.62))->isValid())->toBeTrue();
        expect((new Coordinate(0.0, 0.0))->isValid())->toBeFalse();
    });
});
