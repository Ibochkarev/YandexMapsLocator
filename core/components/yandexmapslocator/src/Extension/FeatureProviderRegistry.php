<?php

declare(strict_types=1);

namespace YandexMapsLocator\Extension;

final class FeatureProviderRegistry
{
    /** @var array<int, FeatureProviderInterface> */
    private array $providers = [];

    public function add(FeatureProviderInterface $provider): void
    {
        $this->providers[spl_object_id($provider)] = $provider;
    }

    /**
     * @return list<FeatureProviderInterface>
     */
    public function all(): array
    {
        return array_values($this->providers);
    }
}
