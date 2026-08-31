<?php

declare(strict_types=1);

namespace YandexMapsLocator\Repository;

use YandexMapsLocator\Model\SearchCriteria;
use YandexMapsLocator\Model\SearchResult;
use YandexMapsLocator\Model\Store;

interface StoreRepositoryInterface
{
    public function search(SearchCriteria $criteria): SearchResult;

    public function findById(int $id, ?string $context = null): ?Store;
}
