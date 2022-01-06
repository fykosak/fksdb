<?php

declare(strict_types=1);

declare(strict_types=1);

namespace FKSDB\Models\SQL;

use NiftyGrid\DataSource\NDataSource;

class SearchableDataSource extends NDataSource
{
    /** @var callback(Selection $table, array $searchTerm) */
    private $filterCallback;

    public function setFilterCallback(callable $filterCallback): void
    {
        $this->filterCallback = $filterCallback;
    }

    public function applyFilter(array $value): void
    {
        ($this->filterCallback)($this->getData(), $value);
    }
}
