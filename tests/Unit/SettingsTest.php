<?php

declare(strict_types=1);

use YandexMapsLocator\Support\Settings;

describe('Settings', function () {
    it('builds underscore system setting keys', function () {
        expect(Settings::key('api_key'))->toBe('yandexmapslocator_api_key')
            ->and(Settings::key('tv_address'))->toBe('yandexmapslocator_tv_address');
    });
});
