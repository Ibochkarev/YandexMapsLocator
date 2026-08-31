<?php

declare(strict_types=1);

namespace YandexMapsLocator\Geo;

use MODX\Revolution\modX;
use YandexMapsLocator\Exception\GeocoderException;
use YandexMapsLocator\Service\CacheService;
use YandexMapsLocator\Support\Settings;

final class YandexGeocoder implements GeocoderInterface
{
    private const GEOCODE_URL = 'https://geocode-maps.yandex.ru/1.x/';

    public function __construct(
        private readonly modX $modx,
        private readonly CacheService $cache,
    ) {
    }

    public function geocode(string $address): ?Coordinate
    {
        $address = trim($address);
        if ($address === '') {
            throw new GeocoderException('Address is empty', 'empty_address');
        }

        $cacheKey = $this->cache->geocodeKey($address);
        $cached = $this->cache->get($cacheKey);
        if (is_array($cached) && isset($cached['latitude'], $cached['longitude'])) {
            return new Coordinate((float) $cached['latitude'], (float) $cached['longitude']);
        }

        $apiKey = (string) Settings::get($this->modx, 'api_key', '');
        if ($apiKey === '') {
            $this->modx->log(modX::LOG_LEVEL_ERROR, '[YandexMapsLocator] API key is not configured');

            throw new GeocoderException('API key is not configured', 'missing_api_key');
        }

        $url = self::GEOCODE_URL . '?' . http_build_query([
            'apikey' => $apiKey,
            'geocode' => $address,
            'format' => 'json',
            'results' => 1,
        ]);

        $response = $this->httpGet($url);
        if ($response === null) {
            throw new GeocoderException('Geocoder request failed', 'network_error');
        }

        $data = json_decode($response, true);
        if (!is_array($data)) {
            throw new GeocoderException('Invalid geocoder response', 'invalid_response');
        }

        $members = $data['response']['GeoObjectCollection']['featureMember'] ?? [];
        if (!is_array($members) || $members === []) {
            return null;
        }

        $point = $members[0]['GeoObject']['Point']['pos'] ?? '';
        if (!is_string($point) || !str_contains($point, ' ')) {
            return null;
        }

        [$longitude, $latitude] = array_map('floatval', explode(' ', $point, 2));
        $coordinate = new Coordinate($latitude, $longitude);

        if (!$coordinate->isValid()) {
            return null;
        }

        $this->cache->set($cacheKey, $coordinate->toArray(), 604800);

        return $coordinate;
    }

    private function httpGet(string $url): ?string
    {
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            if ($ch === false) {
                return null;
            }
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_FOLLOWLOCATION => true,
            ]);
            $body = curl_exec($ch);
            $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($body === false || $code >= 400) {
                $this->modx->log(modX::LOG_LEVEL_ERROR, '[YandexMapsLocator] Geocoder HTTP error: ' . $code);

                return null;
            }

            return (string) $body;
        }

        $context = stream_context_create([
            'http' => [
                'timeout' => 10,
                'ignore_errors' => true,
            ],
        ]);
        $body = @file_get_contents($url, false, $context);

        return $body === false ? null : $body;
    }
}
