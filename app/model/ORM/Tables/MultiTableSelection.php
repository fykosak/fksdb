<?php

namespace FKSDB\ORM\Tables;

use FKSDB\ORM\AbstractServiceMulti;
use Nette\Database\Context;
use Nette\Database\IConventions;
use Nette\Database\Table\Selection as TableSelection;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class MultiTableSelection extends TableSelection {

    /**
     * @var AbstractServiceMulti
     */
    private $service;

    /**
     * MultiTableSelection constructor.
     * @param AbstractServiceMulti $service
     * @param $table
     * @param Context $connection
     * @param IConventions $conventions
     */
    public function __construct(AbstractServiceMulti $service, $table, Context $connection, IConventions $conventions) {
        parent::__construct($connection, $conventions, $table);
        $this->service = $service;
    }

    /**
     * This override ensures returned objects are of correct class.
     *
     * @param array $row
     * @return \FKSDB\ORM\AbstractModelMulti
     */
    protected function createRow(array $row) {
        $mainModel = $this->service->getMainService()->createFromArray($row);
        $joinedModel = $this->service->getJoinedService()->createFromArray($row);
        return $this->service->composeModel($mainModel, $joinedModel);
    }

}

