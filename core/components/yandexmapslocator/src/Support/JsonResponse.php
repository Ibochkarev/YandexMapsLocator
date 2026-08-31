<?php

declare(strict_types=1);

namespace YandexMapsLocator\Support;

use YandexMapsLocator\Frontend\SearchSecurity;

final class JsonResponse
{
    private static ?SearchSecurity $security = null;

    public static function bindSecurity(?SearchSecurity $security): void
    {
        self::$security = $security;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function success(array $data, int $status = 200): never
    {
        self::send([
            'success' => true,
        ] + $data, $status);
    }

    public static function error(string $message, string $code, int $status = 400): never
    {
        self::send([
            'success' => false,
            'error' => $message,
            'code' => $code,
        ], $status);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private static function send(array $payload, int $status): never
    {
        if (!headers_sent()) {
            http_response_code($status);
            header('Content-Type: application/json; charset=utf-8');

            if (self::$security !== null && ($payload['success'] ?? false) === false) {
                self::$security->applyErrorHeaders();
            } elseif (self::$security === null) {
                header('X-Content-Type-Options: nosniff');
            }
        }

        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
