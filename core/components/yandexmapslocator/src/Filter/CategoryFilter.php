<?php

declare(strict_types=1);

namespace YandexMapsLocator\Filter;

use YandexMapsLocator\Model\SearchCriteria;
use YandexMapsLocator\Model\Store;

final class CategoryFilter implements FilterInterface
{
    public function getName(): string
    {
        return 'category';
    }

    public function isOptIn(): bool
    {
        return false;
    }

    /**
     * @param list<Store> $stores
     * @return list<Store>
     */
    public function apply(array $stores, SearchCriteria $criteria): array
    {
        $category = trim($criteria->category);
        if ($category === '') {
            return $stores;
        }

        return array_values(array_filter(
            $stores,
            static fn (Store $store): bool => strcasecmp($store->category, $category) === 0,
        ));
    }
}
