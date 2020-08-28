<?php

namespace FKSDB\SQL;

use NiftyGrid\DataSource\NDataSource;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class ViewDataSource extends NDataSource {

    private string $primaryKey;

    /**
     * ViewDataSource constructor.
     * @param string $primaryKey
     * @param string $table
     */
    public function __construct(string $primaryKey, $table) {
        parent::__construct($table);
        $this->primaryKey = $primaryKey;
    }

    public function getPrimaryKey(): string {
        return $this->primaryKey;
    }
}
