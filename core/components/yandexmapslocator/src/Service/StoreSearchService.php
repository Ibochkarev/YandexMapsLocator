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
        $tvs = InputParser::list($criteria->includeTVs);
        $changed = false;

        if ($criteria->productId > 0 || str_contains($criteria->filters, 'minishop_product')) {
            foreach (['ms3_product_id', 'ms3_product_ids'] as $tv) {
                if (!in_array($tv, $tvs, true)) {
                    $tvs[] = $tv;
                    $changed = true;
                }
            }
        }

        if (InputParser::list($criteria->amenities) !== [] || str_contains($criteria->filters, 'amenity')) {
            if (!in_array('yandexmaps_amenities', $tvs, true)) {
                $tvs[] = 'yandexmaps_amenities';
                $changed = true;
            }
        }

        if (trim($criteria->brand) !== '') {
            if (!in_array('yandexmaps_brand', $tvs, true)) {
                $tvs[] = 'yandexmaps_brand';
                $changed = true;
            }
        }

        if (!$changed) {
            return $criteria;
        }

        return $criteria->withIncludeTvs(implode(',', $tvs));
    }
}
