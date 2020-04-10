<?php

namespace FKSDB\ORM\Tables;

use Nette\Database\Context;
use Nette\Database\IConventions;
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
     * @param Context $connection
     * @param IConventions $conventions
     */
    public function __construct(string $modelClassName, string $table, Context $connection, IConventions $conventions) {
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

