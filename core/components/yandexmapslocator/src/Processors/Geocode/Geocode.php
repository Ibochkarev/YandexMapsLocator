<?php

declare(strict_types=1);

namespace YandexMapsLocator\Processors\Geocode;

use MODX\Revolution\Processors\Processor;
use YandexMapsLocator\Exception\GeocoderException;
use YandexMapsLocator\Support\LocatorService;

class Geocode extends Processor
{
    public function process()
    {
        /** @var \MODX\Revolution\modX $modx */
        $modx = $this->modx;

        try {
            $locator = LocatorService::get($modx);
        } catch (\RuntimeException) {
            return $this->failure('YandexMapsLocator service is not registered');
        }

        $address = trim((string) ($this->getProperty('address', '')));
        if ($address === '') {
            return $this->failure($modx->lexicon('yandexmapslocator_err_empty_address'));
        }

        try {
            $coordinate = $locator->getGeocoder()->geocode($address);
        } catch (GeocoderException $e) {
            return $this->failure($e->getMessage());
        }

        if ($coordinate === null) {
            return $this->failure($modx->lexicon('yandexmapslocator_err_address_not_found'));
        }

        return $this->success('', $coordinate->toArray());
    }
}
