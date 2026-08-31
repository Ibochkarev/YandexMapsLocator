<?php

declare(strict_types=1);

namespace YandexMapsLocator\Repository;

use MODX\Revolution\modResource;
use MODX\Revolution\modX;
use YandexMapsLocator\Filter\FilterManager;
use YandexMapsLocator\Geo\Coordinate;
use YandexMapsLocator\Geo\DistanceCalculatorInterface;
use YandexMapsLocator\Geo\HaversineDistanceCalculator;
use YandexMapsLocator\Model\SearchCriteria;
use YandexMapsLocator\Model\SearchResult;
use YandexMapsLocator\Model\Store;
use YandexMapsLocator\Support\ContextResolver;
use YandexMapsLocator\Support\InputParser;
use YandexMapsLocator\Support\MediaUrl;
use YandexMapsLocator\Support\Settings;
use YandexMapsLocator\Support\TvNames;

final class ResourceStoreRepository implements StoreRepositoryInterface
{
    public function __construct(
        private readonly modX $modx,
        private readonly TvNames $tvNames,
        private readonly FilterManager $filterManager,
        private readonly TvValueLoader $tvLoader,
        private readonly DistanceCalculatorInterface $distanceCalculator = new HaversineDistanceCalculator(),
    ) {
    }

    public function search(SearchCriteria $criteria): SearchResult
    {
        if ($this->canPaginateInSql($criteria)) {
            return $this->searchWithSqlPagination($criteria);
        }

        $all = $this->collectStores($criteria);
        $total = count($all);

        return new SearchResult($this->pageStores($all, $criteria), $total, $criteria);
    }

    public function findById(int $id, ?string $context = null): ?Store
    {
        $contextKeys = $this->resolveContextKeys(
            SearchCriteria::fromArray(['context' => (string) ($context ?? '')]),
        );

        $where = [
            'id' => $id,
            'published' => true,
            'deleted' => false,
        ];
        $this->applyContextFilter($where, $contextKeys);

        /** @var modResource|null $resource */
        $resource = $this->modx->getObject(modResource::class, $where);

        if (!$resource instanceof modResource) {
            return null;
        }

        $tvMap = $this->tvNames->all();
        $tvValues = $this->tvLoader->load([$id], array_values($tvMap));

        return $this->buildStore($resource, $tvValues[$id] ?? [], $tvMap, []);
    }

    private function searchWithSqlPagination(SearchCriteria $criteria): SearchResult
    {
        $parents = InputParser::ids($criteria->parents);
        if ($parents === []) {
            return new SearchResult([], 0, $criteria);
        }

        $total = $this->countValidStores($criteria, $parents);
        $resources = $this->queryResources($criteria, $parents, true);
        if ($resources === []) {
            return new SearchResult([], $total, $criteria);
        }

        $stores = $this->hydrateStores($resources, $criteria, null);
        $stores = $this->filterManager->apply($stores, $criteria);
        $stores = $this->sortStores($stores, $criteria, null);

        return new SearchResult($this->assignIdx($stores, $criteria->offset), $total, $criteria);
    }

    /**
     * @return list<Store>
     */
    private function collectStores(SearchCriteria $criteria): array
    {
        $parents = InputParser::ids($criteria->parents);
        if ($parents === []) {
            return [];
        }

        $origin = $this->resolveOrigin($criteria);
        $resources = $this->queryResources($criteria, $parents, false);
        if ($resources === []) {
            return [];
        }

        $stores = $this->hydrateStores($resources, $criteria, $origin);
        $stores = $this->filterManager->apply($stores, $criteria);

        return $this->sortStores($stores, $criteria, $origin);
    }

    /**
     * @param list<int> $parents
     * @return list<modResource>
     */
    private function queryResources(SearchCriteria $criteria, array $parents, bool $paginate): array
    {
        $c = $this->buildResourceQuery($criteria, $parents);

        if ($paginate) {
            if ($criteria->limit > 0) {
                $this->applyQueryLimit($c, $criteria->limit, $criteria->offset);
            } elseif ($criteria->offset > 0) {
                $this->applyQueryLimit($c, 0, $criteria->offset);
            }
        }

        /** @var modResource[] $resources */
        $resources = $this->modx->getCollection(modResource::class, $c);

        return $resources === [] ? [] : array_values($resources);
    }

    /**
     * @param list<int> $parents
     */
    private function countValidStores(SearchCriteria $criteria, array $parents): int
    {
        $resources = $this->queryResources($criteria, $parents, false);
        if ($resources === []) {
            return 0;
        }

        $tvMap = $this->tvNames->all();
        $resourceIds = array_map(static fn (modResource $r): int => (int) $r->get('id'), $resources);
        $tvValues = $this->tvLoader->load($resourceIds, [
            $tvMap['latitude'],
            $tvMap['longitude'],
        ]);

        $count = 0;
        foreach ($resourceIds as $id) {
            $values = $tvValues[$id] ?? [];
            $lat = $this->toFloat($values[$tvMap['latitude']] ?? null);
            $lng = $this->toFloat($values[$tvMap['longitude']] ?? null);
            if ($lat !== null && $lng !== null) {
                ++$count;
            }
        }

        return $count;
    }

    /**
     * @param list<int> $parents
     */
    private function buildResourceQuery(SearchCriteria $criteria, array $parents): object
    {
        $sortby = $criteria->sortby;
        $sortdir = strtoupper($criteria->sortdir) === 'DESC' ? 'DESC' : 'ASC';
        $where = InputParser::jsonArray($criteria->where);

        $c = $this->modx->newQuery(modResource::class);
        $where = [
            'parent:IN' => $parents,
            'published' => true,
            'deleted' => false,
        ];
        $this->applyContextFilter($where, $this->resolveContextKeys($criteria));
        $c->where($where);

        if ($where !== []) {
            $c->where($where);
        }

        if ($sortby !== 'distance') {
            $c->sortby($this->mapSortField($sortby), $sortdir);
        } else {
            $c->sortby('pagetitle', 'ASC');
        }

        return $c;
    }

    /**
     * @param list<modResource> $resources
     * @return list<Store>
     */
    private function hydrateStores(array $resources, SearchCriteria $criteria, ?Coordinate $origin): array
    {
        $includeTvs = InputParser::list($criteria->includeTVs);
        $radius = $this->effectiveRadius($criteria, $origin);
        $tvMap = $this->tvNames->all();
        $boundingBox = $this->resolveBoundingBox($origin, $radius);

        $resourceIds = array_map(static fn (modResource $r): int => (int) $r->get('id'), $resources);
        $tvValues = $this->tvLoader->load($resourceIds, array_merge(array_values($tvMap), $includeTvs));

        $stores = [];
        foreach ($resources as $resource) {
            $id = (int) $resource->get('id');
            $values = $tvValues[$id] ?? [];

            $store = $this->buildStore($resource, $values, $tvMap, $includeTvs);
            if ($store === null) {
                continue;
            }

            if ($boundingBox !== null && !$this->isInsideBoundingBox($store, $boundingBox)) {
                continue;
            }

            if ($origin !== null) {
                $distance = $this->distanceCalculator->calculate(
                    $origin->latitude,
                    $origin->longitude,
                    $store->latitude,
                    $store->longitude,
                );
                $store = $store->withDistance($distance);

                if ($radius > 0 && $distance > $radius) {
                    continue;
                }
            }

            $stores[] = $store;
        }

        return $stores;
    }

    /**
     * @param list<Store> $stores
     * @return list<Store>
     */
    private function sortStores(array $stores, SearchCriteria $criteria, ?Coordinate $origin): array
    {
        if ($criteria->sortby !== 'distance' || $origin === null) {
            return $stores;
        }

        $sortdir = strtoupper($criteria->sortdir) === 'DESC' ? 'DESC' : 'ASC';
        usort($stores, static function (Store $a, Store $b) use ($sortdir): int {
            $da = $a->distance ?? PHP_FLOAT_MAX;
            $db = $b->distance ?? PHP_FLOAT_MAX;
            $cmp = $da <=> $db;

            return $sortdir === 'DESC' ? -$cmp : $cmp;
        });

        return $stores;
    }

    /**
     * @param list<Store> $stores
     * @return list<Store>
     */
    private function pageStores(array $stores, SearchCriteria $criteria): array
    {
        $offset = $criteria->offset;
        $limit = $criteria->limit;

        if ($offset > 0) {
            $stores = array_slice($stores, $offset);
        }

        if ($limit > 0) {
            $stores = array_slice($stores, 0, $limit);
        }

        return $this->assignIdx($stores, $offset);
    }

    /**
     * @param list<Store> $stores
     * @return list<Store>
     */
    private function assignIdx(array $stores, int $offset): array
    {
        $indexed = [];
        $idx = $offset;
        foreach ($stores as $store) {
            $indexed[] = $store->withIdx(++$idx);
        }

        return $indexed;
    }

    /**
     * @param array<string, string> $values
     * @param array<string, string> $tvMap
     * @param list<string> $includeTvs
     */
    private function buildStore(
        modResource $resource,
        array $values,
        array $tvMap,
        array $includeTvs,
    ): ?Store {
        $id = (int) $resource->get('id');
        $latitude = $this->toFloat($values[$tvMap['latitude']] ?? null);
        $longitude = $this->toFloat($values[$tvMap['longitude']] ?? null);

        if ($latitude === null || $longitude === null) {
            $this->modx->log(
                modX::LOG_LEVEL_WARN,
                '[YandexMapsLocator] Resource ' . $id . ' has invalid coordinates',
            );

            return null;
        }

        $extra = [];
        foreach ($includeTvs as $tvName) {
            if (isset($values[$tvName])) {
                $extra[$tvName] = $values[$tvName];
            }
        }

        $store = new Store(
            id: $id,
            pagetitle: (string) $resource->get('pagetitle'),
            longtitle: (string) $resource->get('longtitle'),
            description: (string) $resource->get('description'),
            url: (string) $this->modx->makeUrl((string) $id, '', 'full', [
                'context_key' => (string) $resource->get('context_key'),
            ]),
            address: (string) ($values[$tvMap['address']] ?? ''),
            latitude: $latitude,
            longitude: $longitude,
            phone: (string) ($values[$tvMap['phone']] ?? ''),
            email: (string) ($values[$tvMap['email']] ?? ''),
            workingHours: (string) ($values[$tvMap['working_hours']] ?? ''),
            category: (string) ($values[$tvMap['category']] ?? ''),
            contextKey: (string) ($resource->get('context_key') ?: 'web'),
            balloonImage: MediaUrl::resolve($this->modx, (string) ($values[$tvMap['balloon_image']] ?? '')),
            markerIcon: MediaUrl::resolve($this->modx, (string) ($values[$tvMap['marker_icon']] ?? '')),
            extra: $extra,
        );

        $params = [
            'store' => &$store,
            'resource' => $resource,
        ];
        $response = $this->modx->invokeEvent('OnYandexMapsLocatorBeforeStorePrepare', $params);
        $store = $this->resolveStoreFromEvent($store, $response);
        $store = $params['store'];

        $params = [
            'store' => &$store,
            'resource' => $resource,
        ];
        $after = $this->modx->invokeEvent('OnYandexMapsLocatorAfterStorePrepare', $params);
        $store = $this->resolveStoreFromEvent($store, $after);
        $store = $params['store'];

        return $store;
    }

    /**
     * @param mixed $response
     */
    private function resolveStoreFromEvent(Store $store, mixed $response): Store
    {
        if (!is_array($response)) {
            return $store;
        }

        foreach ($response as $item) {
            if ($item instanceof Store) {
                $store = $item;
            }
        }

        return $store;
    }

    private function canPaginateInSql(SearchCriteria $criteria): bool
    {
        return $criteria->sortby !== 'distance'
            && !$criteria->hasGeoOrigin()
            && !$criteria->needsGeocoding()
            && trim($criteria->filters) === ''
            && trim($criteria->category) === ''
            && $criteria->productId <= 0;
    }

    private function effectiveRadius(SearchCriteria $criteria, ?Coordinate $origin): float
    {
        if ($origin === null) {
            return 0.0;
        }

        if ($criteria->radius > 0) {
            return $criteria->radius;
        }

        return (float) Settings::get($this->modx, 'default_radius', 50);
    }

    /**
     * @return array{minLat: float, maxLat: float, minLon: float, maxLon: float}|null
     */
    private function resolveBoundingBox(?Coordinate $origin, float $radiusKm): ?array
    {
        if ($origin === null || $radiusKm <= 0 || !$this->distanceCalculator instanceof HaversineDistanceCalculator) {
            return null;
        }

        return $this->distanceCalculator->boundingBox(
            $origin->latitude,
            $origin->longitude,
            $radiusKm,
        );
    }

    /**
     * @param array{minLat: float, maxLat: float, minLon: float, maxLon: float} $box
     */
    private function isInsideBoundingBox(Store $store, array $box): bool
    {
        return $store->latitude >= $box['minLat']
            && $store->latitude <= $box['maxLat']
            && $store->longitude >= $box['minLon']
            && $store->longitude <= $box['maxLon'];
    }

    private function resolveOrigin(SearchCriteria $criteria): ?Coordinate
    {
        $lat = $this->toFloat($criteria->latitude);
        $lng = $this->toFloat($criteria->longitude);
        if ($lat === null || $lng === null) {
            return null;
        }

        $coordinate = new Coordinate($lat, $lng);

        return $coordinate->isValid() ? $coordinate : null;
    }

    /**
     * @return list<string>
     */
    private function resolveContextKeys(SearchCriteria $criteria): array
    {
        $resolver = new ContextResolver($this->modx);

        return $resolver->resolveKeys($criteria->context !== '' ? $criteria->context : null);
    }

    /**
     * @param array<string, mixed> $where
     * @param list<string> $contextKeys
     */
    private function applyContextFilter(array &$where, array $contextKeys): void
    {
        if ($contextKeys === []) {
            $where['context_key'] = (new ContextResolver($this->modx))->defaultKey();

            return;
        }

        if (count($contextKeys) === 1) {
            $where['context_key'] = $contextKeys[0];

            return;
        }

        $where['context_key:IN'] = $contextKeys;
    }

    private function toFloat(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (!is_numeric($value)) {
            return null;
        }

        return (float) $value;
    }

    private function mapSortField(string $sortby): string
    {
        return match ($sortby) {
            'menuindex' => 'menuindex',
            'createdon' => 'createdon',
            'id' => 'id',
            default => 'pagetitle',
        };
    }

    private function applyQueryLimit(object $query, int $limit, int $offset): void
    {
        if (method_exists($query, 'limit')) {
            $query->limit($limit, $offset);
        }
    }
}
