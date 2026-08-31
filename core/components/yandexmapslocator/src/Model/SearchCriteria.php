<?php

declare(strict_types=1);

namespace YandexMapsLocator\Model;

final readonly class SearchCriteria
{
    public function __construct(
        public string $parents = '',
        public int $limit = 0,
        public int $offset = 0,
        public float $radius = 0.0,
        public string $sortby = 'pagetitle',
        public string $sortdir = 'ASC',
        public string $includeTVs = '',
        public string $where = '',
        public string $filters = '',
        public string $category = '',
        public string $latitude = '',
        public string $longitude = '',
        public string $address = '',
        public int $productId = 0,
        public string $context = '',
    ) {
    }

    /**
     * @param array<string, mixed> $props
     */
    public static function fromSnippet(array $props): self
    {
        return self::fromArray([
            'parents' => $props['parents'] ?? '',
            'limit' => (int) ($props['limit'] ?? 0),
            'offset' => (int) ($props['offset'] ?? 0),
            'radius' => (float) ($props['radius'] ?? 0),
            'sortby' => $props['sortby'] ?? 'pagetitle',
            'sortdir' => $props['sortdir'] ?? 'ASC',
            'includeTVs' => $props['includeTVs'] ?? '',
            'where' => $props['where'] ?? '',
            'filters' => $props['filters'] ?? '',
            'category' => $props['category'] ?? '',
            'latitude' => $props['latitude'] ?? '',
            'longitude' => $props['longitude'] ?? '',
            'address' => $props['address'] ?? '',
            'productId' => (int) ($props['productId'] ?? $props['product_id'] ?? 0),
            'context' => $props['context'] ?? $props['contexts'] ?? '',
        ]);
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            parents: (string) ($data['parents'] ?? ''),
            limit: max(0, (int) ($data['limit'] ?? 0)),
            offset: max(0, (int) ($data['offset'] ?? 0)),
            radius: (float) ($data['radius'] ?? 0),
            sortby: (string) ($data['sortby'] ?? 'pagetitle'),
            sortdir: (string) ($data['sortdir'] ?? 'ASC'),
            includeTVs: (string) ($data['includeTVs'] ?? ''),
            where: is_array($data['where'] ?? null)
                ? json_encode($data['where'], JSON_UNESCAPED_UNICODE) ?: ''
                : (string) ($data['where'] ?? ''),
            filters: is_array($data['filters'] ?? null)
                ? json_encode($data['filters'], JSON_UNESCAPED_UNICODE) ?: ''
                : (string) ($data['filters'] ?? ''),
            category: (string) ($data['category'] ?? ''),
            latitude: (string) ($data['latitude'] ?? ''),
            longitude: (string) ($data['longitude'] ?? ''),
            address: (string) ($data['address'] ?? ''),
            productId: max(0, (int) ($data['productId'] ?? $data['product_id'] ?? 0)),
            context: (string) ($data['context'] ?? $data['contexts'] ?? ''),
        );
    }

    /**
     * @param list<int> $parentIds
     */
    public function withParents(array $parentIds): self
    {
        return $this->cloneWith(['parents' => implode(',', $parentIds)]);
    }

    public function withCoordinates(float $latitude, float $longitude): self
    {
        return $this->cloneWith([
            'latitude' => (string) $latitude,
            'longitude' => (string) $longitude,
        ]);
    }

    public function withRadius(float $radius): self
    {
        return $this->cloneWith(['radius' => $radius]);
    }

    public function withIncludeTvs(string $includeTVs): self
    {
        return $this->cloneWith(['includeTVs' => $includeTVs]);
    }

    public function withContext(string $context): self
    {
        return $this->cloneWith(['context' => $context]);
    }

    public function withoutProductId(): self
    {
        if ($this->productId === 0) {
            return $this;
        }

        return $this->cloneWith(['productId' => 0]);
    }

    public function needsGeocoding(): bool
    {
        return $this->address !== '' && $this->latitude === '' && $this->longitude === '';
    }

    public function hasGeoOrigin(): bool
    {
        return trim($this->latitude) !== '' && trim($this->longitude) !== '';
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'parents' => $this->parents,
            'limit' => $this->limit,
            'offset' => $this->offset,
            'radius' => $this->radius,
            'sortby' => $this->sortby,
            'sortdir' => $this->sortdir,
            'includeTVs' => $this->includeTVs,
            'where' => $this->where,
            'filters' => $this->filters,
            'category' => $this->category,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'address' => $this->address,
            'productId' => $this->productId,
            'context' => $this->context,
        ];
    }

    /**
     * @param array<string, mixed> $overrides
     */
    private function cloneWith(array $overrides): self
    {
        return new self(
            parents: (string) ($overrides['parents'] ?? $this->parents),
            limit: (int) ($overrides['limit'] ?? $this->limit),
            offset: (int) ($overrides['offset'] ?? $this->offset),
            radius: (float) ($overrides['radius'] ?? $this->radius),
            sortby: (string) ($overrides['sortby'] ?? $this->sortby),
            sortdir: (string) ($overrides['sortdir'] ?? $this->sortdir),
            includeTVs: (string) ($overrides['includeTVs'] ?? $this->includeTVs),
            where: (string) ($overrides['where'] ?? $this->where),
            filters: (string) ($overrides['filters'] ?? $this->filters),
            category: (string) ($overrides['category'] ?? $this->category),
            latitude: (string) ($overrides['latitude'] ?? $this->latitude),
            longitude: (string) ($overrides['longitude'] ?? $this->longitude),
            address: (string) ($overrides['address'] ?? $this->address),
            productId: (int) ($overrides['productId'] ?? $this->productId),
            context: (string) ($overrides['context'] ?? $this->context),
        );
    }
}
