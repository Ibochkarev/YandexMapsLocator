<?php

declare(strict_types=1);

namespace YandexMapsLocator\Exception;

final class GeocoderException extends \RuntimeException
{
    public function __construct(
        string $message,
        private readonly string $errorCode = 'geocoder_error',
    ) {
        parent::__construct($message);
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }
}
