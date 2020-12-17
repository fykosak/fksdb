<?php

namespace FKSDB\Model\ORM\Tables;

use FKSDB\Model\ORM\Models\AbstractModelSingle;
use Nette\Database\Context;
use Nette\Database\IConventions;
use Nette\Database\Table\Selection;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @template TModel
 */
class TypedTableSelection extends Selection {

    protected string $modelClassName;

    public function __construct(string $modelClassName, string $table, Context $connection, IConventions $conventions) {
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