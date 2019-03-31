<?php

namespace SQL;

use NiftyGrid\DataSource\NDataSource;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class ViewDataSource extends NDataSource {

    private $primaryKey;

    /**
     * ViewDataSource constructor.
     * @param $primaryKey
     * @param $table
     */
    public function __construct($primaryKey, $table) {
        parent::__construct($table);
        $this->primaryKey = $primaryKey;
    }

    /**
     * @return mixed
     */
    public function getPrimaryKey() {
        return $this->primaryKey;
    }

}
