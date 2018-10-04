<?php

namespace ORM\Tables;

use Nette\Database\Connection;
use Nette\Database\IReflection;
use Nette\Database\Table\Selection as TableSelection;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class TypedTableSelection extends TableSelection {

    /**
     * @var string
     */
    protected $modelClassName;

    public function __construct($modelClassName, $table, Connection $connection, IReflection $reflection) {
        parent::__construct($connection, $table, $reflection);
        $this->modelClassName = $modelClassName;
    }

    /**
     * This override ensures returned objects are of correct class.
     *
     * @param array $row
     * @return AbstractModelSingle
     */
    protected function createRow(array $row) {
        $className = $this->modelClassName;
        return new $className($row, $this);
    }

}

