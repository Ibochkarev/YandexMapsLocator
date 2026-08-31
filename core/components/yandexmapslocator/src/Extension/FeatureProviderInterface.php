<?php

declare(strict_types=1);

namespace YandexMapsLocator\Extension;

interface FeatureProviderInterface
{
    /**
     * @return list<string>
     */
    public function capabilities(): array;

    /**
     * @return list<array<string, mixed>>
     */
    public function frontendModules(): array;

    /**
     * @return list<string>
     */
    public function processorActions(): array;

    /**
     * Additional location fields exposed by REST API v1 (?fields=).
     *
     * @return list<string>
     */
    public function apiFields(): array;
}
