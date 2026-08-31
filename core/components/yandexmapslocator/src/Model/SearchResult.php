<?php

declare(strict_types=1);

namespace YandexMapsLocator\Model;

final readonly class SearchResult
{
    /**
     * @param list<Store> $items
     */
    public function __construct(
        public array $items,
        public int $total,
        public SearchCriteria $criteria,
    ) {
    }
}
