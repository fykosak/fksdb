<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Tables;

use FKSDB\Models\ORM\ModelsMulti\ModelMulti;
use FKSDB\Models\ORM\ServicesMulti\ServiceMulti;
use Nette\Database\Conventions;
use Nette\Database\Explorer;
use Nette\Database\Table\Selection;

class MultiTableSelection extends Selection
{

    private ServiceMulti $service;

    public function __construct(
        ServiceMulti $service,
        string $table,
        Explorer $explorer,
        Conventions $conventions
    ) {
        parent::__construct($explorer, $conventions, $table);
        $this->service = $service;
    }

    /**
     * This override ensures returned objects are of correct class.
     */
    protected function createRow(array $row): ModelMulti
    {
        $mainModel = $this->service->mainService->createFromArray($row);
        $joinedModel = $this->service->joinedService->createFromArray($row);
        return $this->service->composeModel($mainModel, $joinedModel);
    }
}
