<?php

declare(strict_types=1);

namespace YandexMapsLocator\Service;

use MODX\Revolution\modX;
use YandexMapsLocator\Geo\Coordinate;
use YandexMapsLocator\Geo\GeocoderInterface;
use YandexMapsLocator\Model\SearchCriteria;
use YandexMapsLocator\Model\SearchResult;
use YandexMapsLocator\Repository\StoreRepositoryInterface;
use YandexMapsLocator\Support\InputParser;

final class StoreSearchService
{
    public function __construct(
        private readonly modX $modx,
        private readonly StoreRepositoryInterface $repository,
        private readonly GeocoderInterface $geocoder,
    ) {
    }

    public function search(SearchCriteria $criteria): SearchResult
    {
        $data = $criteria->toArray();
        $this->modx->invokeEvent('OnYandexMapsLocatorBeforeSearch', [
            'criteria' => &$data,
        ]);

        return $this->executeSearch(SearchCriteria::fromArray($data));
    }

    private function executeSearch(SearchCriteria $criteria): SearchResult
    {
        $criteria = $this->prepareCriteria($criteria);
        $criteria = $this->ensureFilterTvs($criteria);

        $result = $this->repository->search($criteria);

        $stores = $result->items;
        $this->modx->invokeEvent('OnYandexMapsLocatorAfterSearch', [
            'criteria' => $criteria->toArray(),
            'stores' => &$stores,
        ]);

        return new SearchResult($stores, $result->total, $criteria);
    }

    private function prepareCriteria(SearchCriteria $criteria): SearchCriteria
    {
        if ($criteria->needsGeocoding()) {
            $coordinate = $this->geocoder->geocode($criteria->address);
            if ($coordinate instanceof Coordinate) {
                $criteria = $criteria->withCoordinates($coordinate->latitude, $coordinate->longitude);
            }
        }

        return $criteria;
    }

    private function ensureFilterTvs(SearchCriteria $criteria): SearchCriteria
    {
        if ($criteria->productId <= 0 && !str_contains($criteria->filters, 'minishop_product')) {
            return $criteria;
        }

        $tvs = InputParser::list($criteria->includeTVs);
        if (in_array('ms3_product_id', $tvs, true)) {
            return $criteria;
        }

        $tvs[] = 'ms3_product_id';

        return $criteria->withIncludeTvs(implode(',', $tvs));
    }
}
