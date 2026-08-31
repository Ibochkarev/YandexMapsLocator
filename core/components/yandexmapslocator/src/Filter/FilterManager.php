<?php

declare(strict_types=1);

namespace YandexMapsLocator\Filter;

use MODX\Revolution\modX;
use YandexMapsLocator\Model\SearchCriteria;
use YandexMapsLocator\Model\Store;
use YandexMapsLocator\Support\InputParser;

final class FilterManager
{
    /** @var array<string, FilterInterface> */
    private array $filters = [];

    public function __construct(private readonly modX $modx)
    {
        $this->register(new CategoryFilter());
        $this->modx->invokeEvent('OnYandexMapsLocatorRegisterFilters', [
            'registry' => $this,
        ]);
    }

    public function register(FilterInterface $filter): void
    {
        $this->filters[$filter->getName()] = $filter;
    }

    /**
     * @param list<Store> $stores
     * @return list<Store>
     */
    public function apply(array $stores, SearchCriteria $criteria): array
    {
        $active = InputParser::filterNames($criteria->filters);

        foreach ($this->filters as $name => $filter) {
            if ($filter->isOptIn()) {
                if (!in_array($name, $active, true)) {
                    continue;
                }
            } elseif ($active !== [] && !in_array($name, $active, true)) {
                continue;
            }
            $stores = $filter->apply($stores, $criteria);
        }

        return $stores;
    }
}
