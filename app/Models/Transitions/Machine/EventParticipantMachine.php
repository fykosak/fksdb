<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Machine;

use FKSDB\Models\Events\EventDispatchFactory;
use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Services\EventParticipantService;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Transition\Transition;
use FKSDB\Models\Transitions\Transition\UnavailableTransitionsException;
use FKSDB\Models\Utils\FakeStringEnum;
use Fykosak\NetteORM\Model;
use Nette\Database\Explorer;
use Nette\DI\Container;

/**
 * @phpstan-extends Machine<BaseHolder>
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
     * @param BaseHolder $holder
     * @phpstan-return Transition<BaseHolder>[]
     * @phpstan-param (EnumColumn&FakeStringEnum)|null $sourceState
     */
    public function getAvailableTransitions(ModelHolder $holder, ?EnumColumn $sourceState = null): array
    {
        return array_filter(
            $this->getMatchingTransitions($sourceState ?? $holder->getState()),
            fn(Transition $transition): bool => $transition->canExecute($holder)
        );
    }

    /**
     * @phpstan-param FakeStringEnum&EnumColumn $sourceState
     * @phpstan-return Transition<BaseHolder>[]
     */
    private function getMatchingTransitions(EnumColumn $sourceState): array
    {
        return array_filter(
            $this->transitions,
            fn(Transition $transition): bool => $sourceState->value === $transition->source->value
        );
    }

    /**
     * @param EventParticipantModel $model
     */
    public function createHolder(Model $model): BaseHolder
    {
        switch ($model->event->event_type_id) {
            case 2:
            case 14:
                $holder = new BaseHolder($this->eventParticipantService);
                $holder->setModel($model);
                $holder->setEvent($model->event);
                return $holder;
        }
        $holder = $this->eventDispatchFactory->getDummyHolder($model->event);
        $holder->setModel($model);
        return $holder;
    }

    /**
     * @phpstan-param Transition<BaseHolder> $transition
     */
    final public function execute2(Transition $transition, BaseHolder $holder): void
    {
        if (!$transition->canExecute($holder)) {
            throw new UnavailableTransitionsException();
        }
        $holder->setModelState($transition->target);
    }
}
