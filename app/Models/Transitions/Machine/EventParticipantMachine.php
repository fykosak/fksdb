<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Machine;

use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Services\EventParticipantService;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Holder\ParticipantHolder;
use Fykosak\NetteORM\Model\Model;
use Nette\Database\Explorer;

/**
 * @phpstan-extends Machine<ParticipantHolder>
 */
final class EventParticipantMachine extends Machine
{
    private EventParticipantService $eventParticipantService;

    public function __construct(
        Explorer $explorer,
        EventParticipantService $eventParticipantService
    ) {
        parent::__construct($explorer);
        $this->eventParticipantService = $eventParticipantService;
    }

    /**
     * @param EventParticipantModel $model
     * @throws NotImplementedException
     */
    public function createHolder(Model $model): ModelHolder
    {
        switch ($model->event->event_type_id) {
            case 4:
            case 5:
            case 2:
            case 14:
            case 10:
            case 11:
            case 12:
                return new ParticipantHolder($model, $this->eventParticipantService);
        }
        throw new NotImplementedException();
    }
}
