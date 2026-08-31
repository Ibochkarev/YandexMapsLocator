<?php

declare(strict_types=1);

use YandexMapsLocator\Support\JsonResponse;

it('encodes api payload', function () {
    $payload = [
        'success' => true,
        'results' => [['id' => 1, 'pagetitle' => 'Shop']],
    ];
    $json = json_encode($payload, JSON_UNESCAPED_UNICODE);
    expect($json)->toBeString();
    expect(json_decode($json, true)['success'])->toBeTrue();
});

it('json response class exists', function () {
    expect(class_exists(JsonResponse::class))->toBeTrue();
});
