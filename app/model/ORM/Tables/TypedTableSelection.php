<?php

namespace FKSDB\ORM\Tables;

use Nette\Database\Connection;
use Nette\Database\Table\Selection;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @template TModel
 */
class TypedTableSelection extends Selection {

    /**
     * @var string
     */
    protected $modelClassName;

    /**
     * TypedTableSelection constructor.
     * @param $modelClassName
     * @param $table
     * @param Connection $connection
     */
    public function __construct($modelClassName, $table, Connection $connection) {
        parent::__construct($table, $connection);
        $this->modelClassName = $modelClassName;
    }

    /**
     * This override ensures returned objects are of correct class.
     *
     * @param array $row
     * @return \FKSDB\ORM\AbstractModelSingle
     */
    protected function createRow(array $row) {
        $className = $this->modelClassName;
        return new $className($row, $this);
    }

}

