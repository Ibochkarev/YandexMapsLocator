<?php

declare(strict_types=1);

namespace YandexMapsLocator\Model;

use YandexMapsLocator\Support\DistanceFormatter;
use YandexMapsLocator\Support\WorkingHoursFormatter;

final readonly class Store
{
    /**
     * @param array<string, mixed> $extra
     */
    public function __construct(
        public int $id,
        public string $pagetitle,
        public string $longtitle,
        public string $description,
        public string $url,
        public string $address,
        public float $latitude,
        public float $longitude,
        public string $phone,
        public string $email,
        public string $workingHours,
        public string $category,
        public string $contextKey = 'web',
        public ?float $distance = null,
        public int $idx = 0,
        public string $balloonImage = '',
        public string $markerIcon = '',
        public array $extra = [],
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(string $distanceUnit = 'km'): array
    {
        $distanceKm = $this->distance;
        $distanceFormatted = $distanceKm !== null ? DistanceFormatter::format($distanceKm, $distanceUnit) : '';

        return array_merge([
            'id' => $this->id,
            'pagetitle' => $this->pagetitle,
            'longtitle' => $this->longtitle,
            'description' => $this->description,
            'url' => $this->url,
            'address' => $this->address,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'phone' => $this->phone,
            'email' => $this->email,
            'working_hours' => $this->workingHours,
            'working_hours_formatted' => WorkingHoursFormatter::format($this->workingHours),
            'working_hours_compact' => WorkingHoursFormatter::formatCompact($this->workingHours),
            'working_hours_compact_html' => WorkingHoursFormatter::formatCompactHtml($this->workingHours),
            'category' => $this->category,
            'context_key' => $this->contextKey,
            'distance' => $distanceKm,
            'distance_km' => $distanceKm !== null ? DistanceFormatter::format($distanceKm, 'km') : '',
            'distance_m' => $distanceKm !== null ? DistanceFormatter::format($distanceKm, 'm') : '',
            'distance_formatted' => $distanceFormatted,
            'idx' => $this->idx,
            'balloon_image' => $this->balloonImage,
            'marker_icon' => $this->markerIcon,
        ], $this->extra);
    }

    public function withDistance(float $distanceKm): self
    {
        return new self(
            id: $this->id,
            pagetitle: $this->pagetitle,
            longtitle: $this->longtitle,
            description: $this->description,
            url: $this->url,
            address: $this->address,
            latitude: $this->latitude,
            longitude: $this->longitude,
            phone: $this->phone,
            email: $this->email,
            workingHours: $this->workingHours,
            category: $this->category,
            contextKey: $this->contextKey,
            distance: round($distanceKm, 3),
            idx: $this->idx,
            balloonImage: $this->balloonImage,
            markerIcon: $this->markerIcon,
            extra: $this->extra,
        );
    }

    public function withIdx(int $idx): self
    {
        return new self(
            id: $this->id,
            pagetitle: $this->pagetitle,
            longtitle: $this->longtitle,
            description: $this->description,
            url: $this->url,
            address: $this->address,
            latitude: $this->latitude,
            longitude: $this->longitude,
            phone: $this->phone,
            email: $this->email,
            workingHours: $this->workingHours,
            category: $this->category,
            contextKey: $this->contextKey,
            distance: $this->distance,
            idx: $idx,
            balloonImage: $this->balloonImage,
            markerIcon: $this->markerIcon,
            extra: $this->extra,
        );
    }

    /**
     * @param array<string, mixed> $extra
     */
    public function withExtra(array $extra): self
    {
        return new self(
            id: $this->id,
            pagetitle: $this->pagetitle,
            longtitle: $this->longtitle,
            description: $this->description,
            url: $this->url,
            address: $this->address,
            latitude: $this->latitude,
            longitude: $this->longitude,
            phone: $this->phone,
            email: $this->email,
            workingHours: $this->workingHours,
            category: $this->category,
            contextKey: $this->contextKey,
            distance: $this->distance,
            idx: $this->idx,
            balloonImage: $this->balloonImage,
            markerIcon: $this->markerIcon,
            extra: array_merge($this->extra, $extra),
        );
    }
}
