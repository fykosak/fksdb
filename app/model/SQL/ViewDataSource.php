<?php

namespace SQL;

use NiftyGrid\DataSource\NDataSource;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class ViewDataSource extends NDataSource {
    /** @var string */
    private $primaryKey;

    /**
     * ViewDataSource constructor.
     * @param string $primaryKey
     * @param $table
     */
    public function __construct(string $primaryKey, $table) {
        parent::__construct($table);
        $this->primaryKey = $primaryKey;
    }

    /**
     * @return string
     */
    public function getPrimaryKey(): string {
        return $this->primaryKey;
    }
}
