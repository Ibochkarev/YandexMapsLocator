<?php

declare(strict_types=1);

namespace YandexMapsLocator\Frontend;

use YandexMapsLocator\Exception\GeocoderException;
use YandexMapsLocator\Exception\InvalidContextException;
use YandexMapsLocator\Model\SearchCriteria;
use YandexMapsLocator\Model\Store;
use YandexMapsLocator\Support\ContextResolver;
use YandexMapsLocator\Support\JsonResponse;
use YandexMapsLocator\YandexMapsLocator;

/**
 * Public same-origin search for the Free frontend (locator.js).
 * Not a substitute for REST v1 (Pro).
 */
final class SearchHandler
{
    public function __construct(private readonly YandexMapsLocator $locator)
    {
    }

    public function handle(): void
    {
        $security = new SearchSecurity($this->locator);
        JsonResponse::bindSecurity($security);

        if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
            JsonResponse::error('Method not allowed', 'method_not_allowed', 405);
        }

        $security->handlePreflight();

        $query = $_GET;
        if (isset($query['where']) && trim((string) $query['where']) !== '') {
            JsonResponse::error('where is not allowed', 'where_not_allowed', 400);
        }

        foreach (['include', 'fields', 'route'] as $blocked) {
            if (isset($query[$blocked]) && (string) $query[$blocked] !== '') {
                JsonResponse::error('Parameter not allowed: ' . $blocked, 'invalid_param', 400);
            }
        }

        $parents = trim((string) ($query['parents'] ?? ''));
        if ($parents === '') {
            JsonResponse::error('parents is required', 'parents_required', 400);
        }

        $security->assertRateLimit('search');

        $resolver = new ContextResolver($this->locator->modx);
        try {
            $contextKeys = $resolver->assertAllowed((string) ($query['context'] ?? $query['ctx'] ?? ''));
        } catch (InvalidContextException) {
            JsonResponse::error('Invalid or disallowed context', 'invalid_context', 400);
        }

        $criteria = SearchCriteria::fromArray([
            'parents' => $parents,
            'limit' => (int) ($query['limit'] ?? 0),
            'offset' => (int) ($query['offset'] ?? 0),
            'radius' => (float) ($query['radius'] ?? 0),
            'sortby' => (string) ($query['sortby'] ?? 'pagetitle'),
            'sortdir' => (string) ($query['sortdir'] ?? 'ASC'),
            'category' => (string) ($query['category'] ?? ''),
            'filters' => (string) ($query['filters'] ?? ''),
            'latitude' => (string) ($query['lat'] ?? $query['latitude'] ?? ''),
            'longitude' => (string) ($query['lng'] ?? $query['longitude'] ?? ''),
            'address' => (string) ($query['address'] ?? ''),
            'productId' => (int) ($query['product_id'] ?? $query['productId'] ?? 0),
            'context' => implode(',', $contextKeys),
            'amenities' => (string) ($query['amenity'] ?? $query['amenities'] ?? ''),
            'brand' => (string) ($query['brand'] ?? ''),
        ]);

        if (!$this->locator->extensionApi()->hasCapability('pro')) {
            $criteria = $criteria->withoutProductId();
        }

        try {
            $result = $this->locator->getSearchService()->search($criteria);
        } catch (GeocoderException $e) {
            JsonResponse::error($e->getMessage(), $e->getErrorCode(), 400);
        } catch (\Throwable $e) {
            $this->locator->modx->log(
                \MODX\Revolution\modX::LOG_LEVEL_ERROR,
                '[YandexMapsLocator search] ' . $e->getMessage(),
            );
            JsonResponse::error('Internal server error', 'internal_error', 500);
        }

        $unit = (string) $this->locator->getOption('distance_unit', 'km');
        $rows = array_map(
            static fn (Store $store): array => $store->toArray($unit),
            $result->items,
        );

        $security->applyResponseHeaders();
        JsonResponse::success([
            'data' => $rows,
            'meta' => ['total' => $result->total],
        ]);
    }
}
