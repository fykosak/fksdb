<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Machine;

use FKSDB\Models\ORM\Models\Schedule\PersonScheduleModel;
use FKSDB\Models\ORM\Services\Schedule\PersonScheduleService;
use FKSDB\Models\Transitions\Holder\PersonScheduleHolder;
use Fykosak\NetteORM\Model;
use Nette\Database\Explorer;

/**
 * @phpstan-extends Machine<PersonScheduleHolder>
 */
class PersonScheduleMachine extends Machine
{
    private PersonScheduleService $service;

    public function __construct(Explorer $explorer, PersonScheduleService $service)
    {
        parent::__construct($explorer);
        $this->service = $service;
    }

    /**
     * @param PersonScheduleModel $model
     */
    public function createHolder(Model $model): PersonScheduleHolder
    {
        return new PersonScheduleHolder($model, $this->service);
    }
}
