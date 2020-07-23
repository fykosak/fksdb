<?php

namespace FKSDB\SQL;

use NiftyGrid\DataSource\NDataSource;


/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class SearchableDataSource extends NDataSource {

    /** @var callback(Selection $table, string $searchTerm) */
    private $filterCallback;

    public function getFilterCallback(): callable {
        return $this->filterCallback;
    }

    /**
     * @param callable $filterCallback
     * @return void
     */
    public function setFilterCallback(callable $filterCallback) {
        $this->filterCallback = $filterCallback;
    }

    /**
     * @param mixed $value
     * @return void
     */
    public function applyFilter($value) {
        ($this->filterCallback)($this->getData(), $value);
    }
}
