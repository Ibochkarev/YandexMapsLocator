<?php

declare(strict_types=1);

namespace YandexMapsLocator;

use MODX\Revolution\modX;
use YandexMapsLocator\Extension\FeatureProviderRegistry;
use YandexMapsLocator\Extension\LocatorExtensionApi;
use YandexMapsLocator\Filter\FilterManager;
use YandexMapsLocator\Geo\GeocoderInterface;
use YandexMapsLocator\Geo\HaversineDistanceCalculator;
use YandexMapsLocator\Geo\YandexGeocoder;
use YandexMapsLocator\Map\MapProviderInterface;
use YandexMapsLocator\Map\YandexMapsProvider;
use YandexMapsLocator\Repository\ResourceStoreRepository;
use YandexMapsLocator\Repository\StoreRepositoryInterface;
use YandexMapsLocator\Service\CacheService;
use YandexMapsLocator\Service\StoreSearchService;
use YandexMapsLocator\Support\ChunkRenderer;
use YandexMapsLocator\Support\Settings;
use YandexMapsLocator\Support\TvNames;

final class YandexMapsLocator
{
    public const VERSION = '1.0.0-pl5';

    public modX $modx;

    /** @var array<string, mixed> */
    public array $config = [];

    private ?CacheService $cacheService = null;
    private ?TvNames $tvNames = null;
    private ?FilterManager $filterManager = null;
    private ?StoreRepositoryInterface $storeRepository = null;
    private ?GeocoderInterface $geocoder = null;
    private ?MapProviderInterface $mapProvider = null;
    private ?StoreSearchService $searchService = null;
    private ?ChunkRenderer $chunkRenderer = null;
    private ?LocatorExtensionApi $extensionApi = null;

    public function __construct(modX $modx)
    {
        $this->modx = $modx;
        $corePath = $this->modx->getOption(
            'yandexmapslocator.core_path',
            null,
            MODX_CORE_PATH . 'components/yandexmapslocator/',
        );
        $assetsUrl = $this->modx->getOption(
            'yandexmapslocator.assets_url',
            null,
            $this->modx->getOption('assets_url') . 'components/yandexmapslocator/',
        );

        $this->config = [
            'version' => self::VERSION,
            'corePath' => $corePath,
            'assetsUrl' => $assetsUrl,
            'connectorUrl' => $assetsUrl . 'connector.php',
            'contractVersion' => LocatorExtensionApi::CONTRACT_VERSION,
        ];
    }

    public function getCacheService(): CacheService
    {
        return $this->cacheService ??= new CacheService($this->modx);
    }

    public function getTvNames(): TvNames
    {
        return $this->tvNames ??= new TvNames($this->modx);
    }

    public function getFilterManager(): FilterManager
    {
        return $this->filterManager ??= new FilterManager($this->modx);
    }

    public function getStoreRepository(): StoreRepositoryInterface
    {
        return $this->storeRepository ??= new ResourceStoreRepository(
            $this->modx,
            $this->getTvNames(),
            $this->getFilterManager(),
            new \YandexMapsLocator\Repository\TvValueLoader($this->modx),
        );
    }

    public function getGeocoder(): GeocoderInterface
    {
        return $this->geocoder ??= new YandexGeocoder($this->modx, $this->getCacheService());
    }

    public function getMapProvider(): MapProviderInterface
    {
        return $this->mapProvider ??= new YandexMapsProvider($this->modx, $this->config['assetsUrl']);
    }

    public function getSearchService(): StoreSearchService
    {
        return $this->searchService ??= new StoreSearchService(
            $this->modx,
            $this->getStoreRepository(),
            $this->getGeocoder(),
        );
    }

    public function getChunkRenderer(): ChunkRenderer
    {
        return $this->chunkRenderer ??= new ChunkRenderer($this->modx);
    }

    public function extensionApi(): LocatorExtensionApi
    {
        if ($this->extensionApi !== null) {
            return $this->extensionApi;
        }

        $registry = new FeatureProviderRegistry();
        $this->modx->invokeEvent('OnYandexMapsLocatorRegisterFeatureProviders', [
            'registry' => $registry,
        ]);

        return $this->extensionApi = new LocatorExtensionApi($registry->all());
    }

    /**
     * @return array<string, mixed>
     */
    public function getMapConfig(): array
    {
        $config = $this->getMapProvider()->getMapConfig();
        $config['frontendModules'] = $this->extensionApi()->frontendModules();

        $assetsUrl = rtrim((string) ($config['assetsUrl'] ?? ''), '/');
        $config['searchUrl'] = $assetsUrl !== '' ? $assetsUrl . '/search.php' : '';
        $apiEnabled = filter_var(
            Settings::get($this->modx, 'api_enabled', true),
            FILTER_VALIDATE_BOOLEAN,
        );
        $config['restApi'] = $this->extensionApi()->hasCapability('pro') && $apiEnabled;
        if ($config['restApi']) {
            $proAssetsUrl = rtrim((string) $this->modx->getOption(
                'yandexmapslocatorpro.assets_url',
                null,
                $this->modx->getOption('assets_url') . 'components/yandexmapslocatorpro/',
            ), '/');
            $config['apiUrl'] = $proAssetsUrl . '/api.php';
            $config['apiToken'] = trim((string) Settings::get($this->modx, 'api_token', ''));
        }

        return $config;
    }

    public function getOption(string $key, mixed $default = null): mixed
    {
        return Settings::get($this->modx, $key, $default);
    }
}
