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
        if ($criteria->productId > 0 && isset($this->filters['minishop_product'])) {
            $active[] = 'minishop_product';
        }

        foreach ($this->filters as $name => $filter) {
            if ($filter->isOptIn() && !in_array($name, $active, true)) {
                continue;
            }
            $stores = $filter->apply($stores, $criteria);
        }

        return $stores;
    }

    /**
     * @return list<array{name: string, opt_in: bool, param: string}>
     */
    public function describeFilters(): array
    {
        $params = [
            'category' => 'category',
            'brand' => 'brand',
            'working_now' => 'filters',
            'minishop_product' => 'filters,product_id',
            'amenity' => 'amenity',
        ];

        $result = [];
        foreach ($this->filters as $name => $filter) {
            $result[] = [
                'name' => $name,
                'opt_in' => $filter->isOptIn(),
                'param' => $params[$name] ?? 'filters',
            ];
        }

        return $result;
    }
}
