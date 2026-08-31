<?php

declare(strict_types=1);

namespace YandexMapsLocator\Extension;

final class LocatorExtensionApi
{
    public const CONTRACT_VERSION = 1;

    /**
     * @param list<FeatureProviderInterface> $providers
     */
    public function __construct(
        private readonly array $providers = [],
    ) {
    }

    /**
     * @return list<string>
     */
    public function capabilities(): array
    {
        $capabilities = [];
        foreach ($this->providers as $provider) {
            $capabilities = array_merge($capabilities, $provider->capabilities());
        }

        return array_values(array_unique($capabilities));
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function frontendModules(): array
    {
        $modules = [];
        foreach ($this->providers as $provider) {
            $modules = array_merge($modules, $provider->frontendModules());
        }

        return $modules;
    }

    /**
     * @return list<string>
     */
    public function processorActions(): array
    {
        $actions = [];
        foreach ($this->providers as $provider) {
            $actions = array_merge($actions, $provider->processorActions());
        }

        return array_values(array_unique($actions));
    }

    /**
     * @return list<string>
     */
    public function apiFields(): array
    {
        $fields = [];
        foreach ($this->providers as $provider) {
            $fields = array_merge($fields, $provider->apiFields());
        }

        return array_values(array_unique($fields));
    }

    public function hasCapability(string $capability): bool
    {
        return in_array($capability, $this->capabilities(), true);
    }
}
