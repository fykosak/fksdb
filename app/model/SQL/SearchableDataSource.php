<?php

namespace SQL;

use NiftyGrid\DataSource\NDataSource;


/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class SearchableDataSource extends NDataSource {

    /**
     * @var callback(Selection $table, string $searchTerm)
     */
    private $filterCallback;

    /**
     * @return callable
     */
    public function getFilterCallback() {
        return $this->filterCallback;
    }

    /**
     * @param $filterCallback
     * @return void
     */
    public function setFilterCallback(callable $filterCallback) {
        $this->filterCallback = $filterCallback;
    }

    /**
     * @param $value
     * @return void
     */
    public function applyFilter($value) {
        call_user_func_array($this->filterCallback, [
            $this->getData(),
            $value
        ]);
    }

}
