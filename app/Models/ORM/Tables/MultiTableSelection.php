<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Tables;

use FKSDB\Models\ORM\ModelsMulti\Events\ModelMDsefParticipant;
use FKSDB\Models\ORM\ServicesMulti\Events\ServiceMDsefParticipant;
use Nette\Database\Conventions;
use Nette\Database\Explorer;
use Nette\Database\Table\Selection;

/**
 * @deprecated
 */
class MultiTableSelection extends Selection
{
    private ServiceMDsefParticipant $service;

    public function __construct(
        ServiceMDsefParticipant $service,
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
    protected function createRow(array $row): ModelMDsefParticipant
    {
        $mainModel = $this->service->mainService->createFromArray($row);
        $joinedModel = $this->service->joinedService->createFromArray($row);
        return $this->service->composeModel($mainModel, $joinedModel);
    }
}
