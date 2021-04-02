<?php

namespace FKSDB\Models\SQL;

use NiftyGrid\DataSource\NDataSource;


class SearchableDataSource extends NDataSource {

    /** @var callback(Selection $table, string $searchTerm) */
    private $filterCallback;

    public function getFilterCallback(): callable {
        return $this->filterCallback;
    }

    public function setFilterCallback(callable $filterCallback): void {
        $this->filterCallback = $filterCallback;
    }

    /**
     * @param mixed $value
     * @return void
     */
    public function applyFilter($value): void {
        ($this->filterCallback)($this->getData(), $value);
    }
}
