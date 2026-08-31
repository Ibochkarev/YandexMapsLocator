<?php

declare(strict_types=1);

namespace YandexMapsLocator\Geo;

final class HaversineDistanceCalculator implements DistanceCalculatorInterface
{
    private const EARTH_RADIUS_KM = 6371.0;

    public function calculate(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $lat1Rad = deg2rad($lat1);
        $lat2Rad = deg2rad($lat2);
        $deltaLat = deg2rad($lat2 - $lat1);
        $deltaLon = deg2rad($lon2 - $lon1);

        $a = sin($deltaLat / 2) ** 2
            + cos($lat1Rad) * cos($lat2Rad) * sin($deltaLon / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return self::EARTH_RADIUS_KM * $c;
    }

    /**
     * Approximate bounding box for a radius in km (for SQL pre-filtering).
     *
     * @return array{minLat: float, maxLat: float, minLon: float, maxLon: float}
     */
    public function boundingBox(float $latitude, float $longitude, float $radiusKm): array
    {
        $latDelta = rad2deg($radiusKm / self::EARTH_RADIUS_KM);
        $lonDelta = rad2deg($radiusKm / (self::EARTH_RADIUS_KM * max(cos(deg2rad($latitude)), 0.0001)));

        return [
            'minLat' => $latitude - $latDelta,
            'maxLat' => $latitude + $latDelta,
            'minLon' => $longitude - $lonDelta,
            'maxLon' => $longitude + $lonDelta,
        ];
    }
}
