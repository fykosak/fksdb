<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Model\Holder;

use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Models\EventParticipantStatus;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\EventParticipantService;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Machine\Machine;

/**
 * @phpstan-implements ModelHolder<EventParticipantStatus,EventParticipantModel>
 */
class BaseHolder implements ModelHolder
{
    private EventParticipantService $service;
    private ?EventParticipantModel $model;

    public function __construct(EventParticipantService $service, ?EventParticipantModel $model = null)
    {
        $this->service = $service;
        $this->model = $model;
    }

    public function getModel(): ?EventParticipantModel
    {
        return $this->model ?? null;
    }

    public function setModel(?EventParticipantModel $model): void
    {
        $this->model = $model;
    }

    public function getModelState(): EventParticipantStatus
    {
        $model = $this->getModel();
        if ($model) {
            return $model->status;
        }

        return EventParticipantStatus::from(Machine::STATE_INIT);
    }

    public function getPerson(): ?PersonModel
    {
        $app = $this->getModel();
        if (!$app) {
            return null;
        }
        return $app->person;
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
