<?php

declare(strict_types=1);

namespace YandexMapsLocator\Snippet;

use MODX\Revolution\modX;
use YandexMapsLocator\Model\SearchCriteria;
use YandexMapsLocator\Model\Store;
use YandexMapsLocator\Support\LocatorService;
use YandexMapsLocator\YandexMapsLocator;

final class YandexMapsLocatorSnippet extends AbstractSnippet
{
    /**
     * @param array<string, mixed> $scriptProperties
     */
    public function process(array $scriptProperties): string
    {
        $locator = $this->locator();
        $criteria = $this->resolveCriteria($scriptProperties, $locator);
        $return = (string) ($scriptProperties['return'] ?? 'chunks');

        try {
            $result = $locator->getSearchService()->search($criteria);
            $stores = $result->items;
            $criteria = $result->criteria;
        } catch (\Throwable $e) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, '[YandexMapsLocator] ' . $e->getMessage());

            return $this->renderError($scriptProperties, $e->getMessage(), $locator);
        }

        return match ($return) {
            'json' => $this->renderJson($stores, $locator),
            'data' => $this->renderData($stores, $locator),
            default => $this->renderChunks($stores, $scriptProperties, $locator, $criteria),
        };
    }

    /**
     * @param list<Store> $stores
     */
    private function renderJson(array $stores, YandexMapsLocator $locator): string
    {
        return json_encode(
            ['success' => true, 'results' => $this->storesToArray($stores, $locator)],
            JSON_UNESCAPED_UNICODE,
        ) ?: '';
    }

    /**
     * @param list<Store> $stores
     */
    private function renderData(array $stores, YandexMapsLocator $locator): string
    {
        $rows = $this->storesToArray($stores, $locator);
        $this->modx->setPlaceholder('yandexmapslocator.stores', $rows);
        $this->modx->setPlaceholder('yandexmapslocator.count', count($rows));

        return '';
    }

    /**
     * @param list<Store> $stores
     * @param array<string, mixed> $scriptProperties
     */
    private function renderChunks(
        array $stores,
        array $scriptProperties,
        YandexMapsLocator $locator,
        SearchCriteria $criteria,
    ): string {
        $chunks = $locator->getChunkRenderer();
        $chunks->ensureLexiconLoaded();

        $tpl = (string) ($scriptProperties['tpl'] ?? 'yandexmapslocator.store');
        $tplOuter = (string) ($scriptProperties['tplOuter'] ?? 'yandexmapslocator.outer');
        $tplSearch = (string) ($scriptProperties['tplSearch'] ?? 'yandexmapslocator.search');
        $tplEmpty = (string) ($scriptProperties['tplEmpty'] ?? 'yandexmapslocator.empty');
        $unit = (string) $locator->getOption('distance_unit', 'km');

        $storesHtml = $stores === []
            ? $chunks->render($tplEmpty, [])
            : $chunks->renderStores($stores, $tpl, $unit);

        $mapConfig = $locator->getMapConfig();
        $mapConfig['center'] = $this->resolveMapCenter($stores, $criteria, $mapConfig);

        $mapConfig['parents'] = $criteria->parents;
        $mapConfig['context'] = $criteria->context;
        $mapConfig['i18n'] = $this->frontendI18n();

        return $chunks->render($tplOuter, [
            'search' => $chunks->render($tplSearch, [
                'api_url' => $mapConfig['apiUrl'] ?? '',
                'address' => $criteria->address,
            ]),
            'stores' => $storesHtml,
            'parents' => $criteria->parents,
            'is_empty' => $stores === [],
            'map_config' => json_encode($mapConfig, JSON_UNESCAPED_UNICODE) ?: '{}',
            'stores_json' => json_encode($this->storesToArray($stores, $locator), JSON_UNESCAPED_UNICODE) ?: '[]',
            'assets_url' => $locator->config['assetsUrl'] ?? '',
            'assets_version' => (string) ($locator->config['version'] ?? YandexMapsLocator::VERSION),
        ]);
    }

    /**
     * @param array<string, mixed> $scriptProperties
     */
    private function resolveCriteria(array $scriptProperties, YandexMapsLocator $locator): SearchCriteria
    {
        $criteria = SearchCriteria::fromSnippet($scriptProperties);
        $resolver = new \YandexMapsLocator\Support\ContextResolver($this->modx);

        try {
            $keys = $resolver->assertAllowed($criteria->context !== '' ? $criteria->context : null);
        } catch (\YandexMapsLocator\Exception\InvalidContextException) {
            throw new \RuntimeException('Invalid or disallowed MODX context');
        }

        return $criteria->withContext(implode(',', $keys));
    }

    /**
     * @param list<Store> $stores
     * @param array<string, mixed> $mapConfig
     * @return array{latitude: float, longitude: float}
     */
    private function resolveMapCenter(array $stores, SearchCriteria $criteria, array $mapConfig): array
    {
        if ($criteria->hasGeoOrigin()) {
            return [
                'latitude' => (float) $criteria->latitude,
                'longitude' => (float) $criteria->longitude,
            ];
        }

        if ($stores === []) {
            /** @var array{latitude: float, longitude: float} $center */
            $center = $mapConfig['center'] ?? ['latitude' => 55.751244, 'longitude' => 37.618423];

            return $center;
        }

        if (count($stores) === 1) {
            return [
                'latitude' => $stores[0]->latitude,
                'longitude' => $stores[0]->longitude,
            ];
        }

        $latSum = 0.0;
        $lngSum = 0.0;
        foreach ($stores as $store) {
            $latSum += $store->latitude;
            $lngSum += $store->longitude;
        }
        $count = count($stores);

        return [
            'latitude' => $latSum / $count,
            'longitude' => $lngSum / $count,
        ];
    }

    /**
     * @return array<string, string>
     */
    private function frontendI18n(): array
    {
        return [
            'empty' => (string) $this->modx->lexicon('yandexmapslocator_empty'),
            'route' => (string) $this->modx->lexicon('yandexmapslocator_route'),
            'showOnMap' => (string) $this->modx->lexicon('yandexmapslocator_show_on_map'),
            'locateMe' => (string) $this->modx->lexicon('yandexmapslocator_locate_me'),
            'showAll' => (string) $this->modx->lexicon('yandexmapslocator_show_all'),
            'openNow' => (string) $this->modx->lexicon('yandexmapslocator_open_now'),
            'closedNow' => (string) $this->modx->lexicon('yandexmapslocator_closed_now'),
            'filterOpenNow' => (string) $this->modx->lexicon('yandexmapslocator_filter_open_now'),
            'searching' => (string) $this->modx->lexicon('yandexmapslocator_searching'),
            'errEmptyAddress' => (string) $this->modx->lexicon('yandexmapslocator_err_empty_address'),
            'errGeolocationDenied' => (string) $this->modx->lexicon('yandexmapslocator_err_geolocation_denied'),
            'errGeolocationUnavailable' => (string) $this->modx->lexicon('yandexmapslocator_err_geolocation_unavailable'),
            'errGeolocationTimeout' => (string) $this->modx->lexicon('yandexmapslocator_err_geolocation_timeout'),
            'errGeolocationFailed' => (string) $this->modx->lexicon('yandexmapslocator_err_geolocation_failed'),
            'errGeolocationUnsupported' => (string) $this->modx->lexicon('yandexmapslocator_err_geolocation_unsupported'),
            'errRateLimit' => (string) $this->modx->lexicon('yandexmapslocator_err_rate_limit'),
            'errSearchFailed' => (string) $this->modx->lexicon('yandexmapslocator_err_search_failed'),
            'errInvalidResponse' => (string) $this->modx->lexicon('yandexmapslocator_err_invalid_response'),
            'errProRequired' => (string) $this->modx->lexicon('yandexmapslocator_err_pro_required'),
        ];
    }

    /**
     * @param array<string, mixed> $scriptProperties
     */
    private function renderError(array $scriptProperties, string $message, YandexMapsLocator $locator): string
    {
        $chunks = $locator->getChunkRenderer();
        $chunks->ensureLexiconLoaded();
        $tplError = (string) ($scriptProperties['tplError'] ?? 'yandexmapslocator.error');

        return $chunks->render($tplError, ['error' => $message]);
    }

    /**
     * @param list<Store> $stores
     * @return list<array<string, mixed>>
     */
    private function storesToArray(array $stores, YandexMapsLocator $locator): array
    {
        $unit = (string) $locator->getOption('distance_unit', 'km');

        return array_map(static fn (Store $store): array => $store->toArray($unit), $stores);
    }
}
