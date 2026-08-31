<?php

declare(strict_types=1);

namespace YandexMapsLocator\Map;

interface MapProviderInterface
{
    /**
     * @return array<string, mixed>
     */
    public function getMapConfig(): array;

    /**
     * @return array<string, mixed>
     */
    public function getMarkerOptions(): array;
}
