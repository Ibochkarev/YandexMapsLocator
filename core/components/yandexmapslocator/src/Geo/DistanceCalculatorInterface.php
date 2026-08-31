<?php

declare(strict_types=1);

namespace YandexMapsLocator\Geo;

interface DistanceCalculatorInterface
{
    /**
     * Distance in kilometers.
     */
    public function calculate(float $lat1, float $lon1, float $lat2, float $lon2): float;
}
