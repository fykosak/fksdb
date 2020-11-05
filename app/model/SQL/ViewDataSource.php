<?php

namespace FKSDB\SQL;

use Nette\Database\Table\Selection;
use NiftyGrid\DataSource\NDataSource;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class ViewDataSource extends NDataSource {

    private string $primaryKey;

    public function __construct(string $primaryKey, Selection $table) {
        parent::__construct($table);
        $this->primaryKey = $primaryKey;
    }

    public function getPrimaryKey(): string {
        return $this->primaryKey;
    }
}
