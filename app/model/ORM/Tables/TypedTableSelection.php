<?php

namespace FKSDB\ORM\Tables;

use Nette\Database\Connection;
use Nette\Database\Context;
use Nette\Database\IConventions;
use Nette\Database\Table\Selection as TableSelection;

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
     * @param Context $connection
     */
    public function __construct($modelClassName, $table, Context $connection, IConventions $conventions) {
        parent::__construct($connection, $conventions, $table);
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

