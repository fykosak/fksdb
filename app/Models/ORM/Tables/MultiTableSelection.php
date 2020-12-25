<?php

namespace FKSDB\Models\ORM\Tables;

use FKSDB\Models\ORM\ModelsMulti\AbstractModelMulti;
use FKSDB\Models\ORM\ServicesMulti\AbstractServiceMulti;
use Nette\Database\Conventions;
use Nette\Database\Explorer;
use Nette\Database\Table\Selection;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class MultiTableSelection extends Selection {

    private AbstractServiceMulti $service;

    public function __construct(AbstractServiceMulti $service, string $table, Explorer $connection, Conventions $conventions) {
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
