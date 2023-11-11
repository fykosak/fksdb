<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Holder;

use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\ORM\Models\Schedule\PersonScheduleModel;
use FKSDB\Models\ORM\Models\Schedule\PersonScheduleState;
use FKSDB\Models\ORM\Services\Schedule\PersonScheduleService;

/**
 * @phpstan-implements ModelHolder<PersonScheduleState,PersonScheduleModel>
 */
class PersonScheduleHolder implements ModelHolder
{
    private PersonScheduleService $service;
    private PersonScheduleModel $model;

    public function __construct(PersonScheduleModel $model, PersonScheduleService $service)
    {
        $this->service = $service;
        $this->model = $model;
    }

    public function getModel(): ?PersonScheduleModel
    {
        return $this->model;
    }

    /**
     * @phpstan-param PersonScheduleState $newState
     */
    public function updateState(EnumColumn $newState): void
    {
        $this->service->storeModel(['state' => $newState->value], $this->model);
    }

    public function getState(): PersonScheduleState
    {
        return $this->model->state;
    }
}
