<?php

declare(strict_types=1);

namespace YandexMapsLocator\Geo;

final readonly class Coordinate
{
    public function __construct(
        public float $latitude,
        public float $longitude,
    ) {
    }

    public function isValid(): bool
    {
        return $this->latitude >= -90.0
            && $this->latitude <= 90.0
            && $this->longitude >= -180.0
            && $this->longitude <= 180.0
            && !($this->latitude === 0.0 && $this->longitude === 0.0);
    }

    /**
     * @return array{latitude: float, longitude: float}
     */
    public function toArray(): array
    {
        return [
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ];
    }
}
