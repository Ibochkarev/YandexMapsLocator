<?php

declare(strict_types=1);

namespace YandexMapsLocator\Support;

final class DistanceFormatter
{
    public static function format(float $distanceKm, string $unit): string
    {
        if ($unit === 'm') {
            return (int) round($distanceKm * 1000) . ' м';
        }

        return round($distanceKm, 1) . ' км';
    }
}
