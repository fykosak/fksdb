<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Machine;

use FKSDB\Models\Events\EventDispatchFactory;
use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Models\EventParticipantStatus;
use FKSDB\Models\ORM\Services\EventParticipantService;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Holder\ParticipantHolder;
use FKSDB\Models\Transitions\Transition\Transition;
use Fykosak\NetteORM\Model\Model;
use Nette\Database\Explorer;

/**
 * @phpstan-template THolder of BaseHolder|ParticipantHolder
 * @phpstan-extends Machine<THolder>
 */
final class EventParticipantMachine extends Machine
{
    private EventDispatchFactory $eventDispatchFactory;
    private EventParticipantService $eventParticipantService;

    public function __construct(
        EventDispatchFactory $eventDispatchFactory,
        Explorer $explorer,
        EventParticipantService $eventParticipantService
    ) {
        parent::__construct($explorer);
        $this->eventDispatchFactory = $eventDispatchFactory;
        $this->eventParticipantService = $eventParticipantService;
    }

    /**
     * @phpstan-param THolder $holder
     * @phpstan-return Transition<THolder>[]
     * @phpstan-param EventParticipantStatus|null $sourceState
     */
    public function getAvailableTransitions(ModelHolder $holder, ?EnumColumn $sourceState = null): array
    {
        return array_filter(
            $this->getMatchingTransitions($sourceState ?? $holder->getState()),
            fn(Transition $transition): bool => $transition->canExecute($holder)
        );
    }

    /**
     * @phpstan-return Transition<THolder>[]
     */
    private function getMatchingTransitions(EventParticipantStatus $sourceState): array
    {
        return array_filter(
            $this->transitions,
            fn(Transition $transition): bool => $sourceState->value === $transition->source->value
        );
    }

    /**
     * @param EventParticipantModel $model
     */
    public function createHolder(Model $model): ModelHolder
    {
        switch ($model->event->event_type_id) {
            case 2:
            case 14:
            case 10:
            case 11:
            case 12:
                return new ParticipantHolder($model, $this->eventParticipantService);
        }
        return new BaseHolder($this->eventParticipantService, $model);
    }
}
