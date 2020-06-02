<?php

namespace FKSDB\ORM\Tables;

use FKSDB\ORM\AbstractModelMulti;
use FKSDB\ORM\AbstractServiceMulti;
use Nette\Database\Context;
use Nette\Database\IConventions;
use Nette\Database\Table\Selection;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class MultiTableSelection extends Selection {

    private AbstractServiceMulti $service;

    /**
     * MultiTableSelection constructor.
     * @param AbstractServiceMulti $service
     * @param $table
     * @param Context $connection
     * @param IConventions $conventions
     */
    public function __construct(AbstractServiceMulti $service, string $table, Context $connection, IConventions $conventions) {
        parent::__construct($connection, $conventions, $table);
        $this->service = $service;
    }

    /**
     * This override ensures returned objects are of correct class.
     *
     * @param array $row
     * @return AbstractModelMulti
     */
    protected function createRow(array $row): AbstractModelMulti {
        $mainModel = $this->service->getMainService()->createFromArray($row);
        $joinedModel = $this->service->getJoinedService()->createFromArray($row);
        return $this->service->composeModel($mainModel, $joinedModel);
    }
}
