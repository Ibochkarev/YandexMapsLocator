<?php

declare(strict_types=1);

namespace YandexMapsLocator\Support;

use MODX\Revolution\modX;

final class TvNames
{
    /** @var array<string, string>|null */
    private ?array $cache = null;

    public function __construct(private readonly modX $modx)
    {
    }

    /**
     * @return array{address: string, latitude: string, longitude: string, phone: string, email: string, working_hours: string, category: string, balloon_image: string, marker_icon: string}
     */
    public function all(): array
    {
        if ($this->cache !== null) {
            return $this->cache;
        }

        $this->cache = [
            'address' => (string) Settings::get($this->modx, 'tv_address', 'yandexmaps_address'),
            'latitude' => (string) Settings::get($this->modx, 'tv_latitude', 'yandexmaps_latitude'),
            'longitude' => (string) Settings::get($this->modx, 'tv_longitude', 'yandexmaps_longitude'),
            'phone' => (string) Settings::get($this->modx, 'tv_phone', 'yandexmaps_phone'),
            'email' => (string) Settings::get($this->modx, 'tv_email', 'yandexmaps_email'),
            'working_hours' => (string) Settings::get($this->modx, 'tv_working_hours', 'yandexmaps_working_hours'),
            'category' => (string) Settings::get($this->modx, 'tv_category', 'yandexmaps_category'),
            'balloon_image' => (string) Settings::get($this->modx, 'tv_balloon_image', 'yandexmaps_balloon_image'),
            'marker_icon' => (string) Settings::get($this->modx, 'tv_marker_icon', 'yandexmaps_marker_icon'),
        ];

        return $this->cache;
    }

    public function get(string $key): string
    {
        $all = $this->all();

        return $all[$key] ?? '';
    }
}
