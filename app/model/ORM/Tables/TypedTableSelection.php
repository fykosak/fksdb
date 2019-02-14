<?php

namespace ORM\Tables;

use Nette\Database\Table\Selection as TableSelection;
use Nette\Database\Connection;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class TypedTableSelection extends TableSelection {

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
     * @return \AbstractModelSingle
     */
    protected function createRow(array $row) {
        $className = $this->modelClassName;
        return new $className($row, $this);
    }

}

