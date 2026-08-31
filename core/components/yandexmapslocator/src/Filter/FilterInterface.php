<?php

declare(strict_types=1);

namespace YandexMapsLocator\Filter;

use YandexMapsLocator\Model\SearchCriteria;
use YandexMapsLocator\Model\Store;

interface FilterInterface
{
    public function getName(): string;

    public function isOptIn(): bool;

    /**
     * @param list<Store> $stores
     * @return list<Store>
     */
    public function apply(array $stores, SearchCriteria $criteria): array;
}
