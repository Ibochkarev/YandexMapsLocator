<?php

declare(strict_types=1);

use YandexMapsLocator\Support\ContextResolver;

it('sanitizes bootstrap context keys', function () {
    expect(ContextResolver::sanitizeBootstrapKey(''))->toBe('web')
        ->and(ContextResolver::sanitizeBootstrapKey('en'))->toBe('en')
        ->and(ContextResolver::sanitizeBootstrapKey('ctx-1'))->toBe('ctx-1')
        ->and(ContextResolver::sanitizeBootstrapKey('../web'))->toBe('web');
});

it('stores context in search criteria', function () {
    $criteria = \YandexMapsLocator\Model\SearchCriteria::fromArray([
        'parents' => '1',
        'context' => 'en,de',
    ]);

    expect($criteria->context)->toBe('en,de')
        ->and($criteria->withContext('web')->context)->toBe('web');
});
