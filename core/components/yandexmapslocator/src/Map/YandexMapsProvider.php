<?php

declare(strict_types=1);

namespace YandexMapsLocator\Map;

use MODX\Revolution\modX;
use YandexMapsLocator\Support\MediaUrl;
use YandexMapsLocator\Support\Settings;

final class YandexMapsProvider implements MapProviderInterface
{
    public function __construct(
        private readonly modX $modx,
        private readonly string $assetsUrl,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function getMapConfig(): array
    {
        return [
            'provider' => 'yandex',
            'apiKey' => (string) Settings::get($this->modx, 'api_key', ''),
            'center' => [
                'latitude' => (float) Settings::get($this->modx, 'default_latitude', 55.751244),
                'longitude' => (float) Settings::get($this->modx, 'default_longitude', 37.618423),
            ],
            'zoom' => (int) Settings::get($this->modx, 'default_zoom', 10),
            'cluster' => (bool) Settings::get($this->modx, 'cluster', true),
            'assetsUrl' => $this->assetsUrl,
            'balloon' => [
                'defaultImage' => MediaUrl::resolve(
                    $this->modx,
                    (string) Settings::get($this->modx, 'default_balloon_image', ''),
                ),
            ],
            'markerIconSize' => $this->markerIconSize(),
            'markerOptions' => $this->getMarkerOptions(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getMarkerOptions(): array
    {
        return [
            'preset' => 'islands#redDotIcon',
            // Default Yandex balloon (~250px) clips «Построить маршрут» + image.
            'balloonMaxWidth' => 360,
            'balloonMinWidth' => 220,
        ];
    }

    /**
     * @return array{0: int, 1: int}
     */
    private function markerIconSize(): array
    {
        $raw = trim((string) Settings::get($this->modx, 'marker_icon_size', '32,32'));
        $parts = array_map('trim', explode(',', $raw, 2));
        $width = (int) ($parts[0] !== '' ? $parts[0] : 32);
        $height = (int) (($parts[1] ?? '') !== '' ? $parts[1] : 32);

        if ($width <= 0) {
            $width = 32;
        }
        if ($height <= 0) {
            $height = 32;
        }

        return [$width, $height];
    }
}
