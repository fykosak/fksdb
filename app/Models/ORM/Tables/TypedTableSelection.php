<?php

namespace FKSDB\Models\ORM\Tables;

use FKSDB\Models\ORM\Models\AbstractModelSingle;
use Nette\Database\Conventions;
use Nette\Database\Explorer;
use Nette\Database\Table\Selection;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @template TModel
 */
class TypedTableSelection extends Selection {

    protected string $modelClassName;

    public function __construct(string $modelClassName, string $table, Explorer $connection, Conventions $conventions) {
        parent::__construct($connection, $conventions, $table);
        $this->modelClassName = $modelClassName;
    }

    /**
     * This override ensures returned objects are of correct class.
     *
     * @param array $row
     * @return AbstractModelSingle
     */
    protected function createRow(array $row): AbstractModelSingle {
        $className = $this->modelClassName;
        return new $className($row, $this);
    }
}
