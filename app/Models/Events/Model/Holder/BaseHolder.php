<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Model\Holder;

use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Models\EventParticipantStatus;
use FKSDB\Models\ORM\Services\EventParticipantService;
use FKSDB\Models\Transitions\Holder\ModelHolder;

/**
 * @phpstan-implements ModelHolder<EventParticipantStatus,EventParticipantModel>
 */
class BaseHolder implements ModelHolder
{
    private EventParticipantService $service;
    private EventParticipantModel $model;

    public function __construct(EventParticipantService $service, EventParticipantModel $model)
    {
        $this->service = $service;
        $this->model = $model;
    }

    public function getModel(): EventParticipantModel
    {
        return $this->model;
    }

    /**
     * @phpstan-param EventParticipantStatus $newState
     */
    public function updateState(EnumColumn $newState): void
    {
        $this->service->storeModel(['status' => $newState->value], $this->model);
    }

    public function getState(): EventParticipantStatus
    {
        return $this->model->status;
    }
}
