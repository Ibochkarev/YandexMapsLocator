<?php

declare(strict_types=1);

namespace YandexMapsLocator\Repository;

use MODX\Revolution\modX;

final class TvValueLoader
{
    public function __construct(private readonly modX $modx)
    {
    }

    /**
     * @param list<int> $resourceIds
     * @param list<string> $tvNames
     * @return array<int, array<string, string>>
     */
    public function load(array $resourceIds, array $tvNames): array
    {
        $tvNames = array_values(array_unique(array_filter($tvNames)));
        if ($resourceIds === [] || $tvNames === []) {
            return [];
        }

        $tablePrefix = $this->modx->getOption('table_prefix', null, 'modx_');
        $ids = implode(',', array_map('intval', $resourceIds));
        $names = implode(',', array_map(fn (string $n): string => $this->modx->quote($n), $tvNames));

        $sql = "SELECT c.contentid, t.name, c.value
            FROM {$tablePrefix}site_tmplvar_contentvalues c
            INNER JOIN {$tablePrefix}site_tmplvars t ON t.id = c.tmplvarid
            WHERE c.contentid IN ({$ids}) AND t.name IN ({$names})";

        $result = [];
        $stmt = $this->modx->prepare($sql);
        if (!$stmt || !$stmt->execute()) {
            return [];
        }

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $contentId = (int) $row['contentid'];
            $result[$contentId][(string) $row['name']] = (string) $row['value'];
        }

        return $result;
    }
}
