<?php

namespace FKSDB\Models\ORM\Tables;

use FKSDB\Models\ORM\ModelsMulti\AbstractModelMulti;
use FKSDB\Models\ORM\ServicesMulti\AbstractServiceMulti;
use Nette\Database\Conventions;
use Nette\Database\Explorer;
use Nette\Database\Table\Selection;

class MultiTableSelection extends Selection
{

    private AbstractServiceMulti $service;

    public function __construct(AbstractServiceMulti $service, string $table, Explorer $explorer, Conventions $conventions)
    {
        parent::__construct($explorer, $conventions, $table);
        $this->service = $service;
    }

    /**
     * This override ensures returned objects are of correct class.
     *
     * @param array $row
     * @return AbstractModelMulti
     */
    protected function createRow(array $row): AbstractModelMulti
    {
        $mainModel = $this->service->mainService->createFromArray($row);
        $joinedModel = $this->service->joinedService->createFromArray($row);
        return $this->service->composeModel($mainModel, $joinedModel);
    }
}
