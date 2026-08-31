<?php

declare(strict_types=1);

namespace YandexMapsLocator\Geo;

interface GeocoderInterface
{
    public function geocode(string $address): ?Coordinate;
}
