<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Machine;

use FKSDB\Models\Events\EventDispatchFactory;
use FKSDB\Models\Events\Machine\Transition;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\Expressions\NeonSchemaException;
use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use Fykosak\NetteORM\Model;
use Nette\Database\Explorer;
use Nette\InvalidArgumentException;

/**
 * @property Transition[] $transitions
 */
class EventParticipantMachine extends Machine
{

    public string $name = 'participant';

    private EventDispatchFactory $eventDispatchFactory;

    public function __construct(EventDispatchFactory $eventDispatchFactory, Explorer $explorer)
    {
        parent::__construct($explorer);
        $this->eventDispatchFactory = $eventDispatchFactory;
    }

    /**
     * @throws BadTypeException
     */
    public function addTransition(\FKSDB\Models\Transitions\Transition\Transition $transition): void
    {
        if (!$transition instanceof Transition) {
            throw new BadTypeException(Transition::class, $transition);
        }
        $transition->setBaseMachine($this);
        $this->transitions[$transition->getId()] = $transition;
    }

    /**
     * @return Transition[]
     */
    public function getAvailableTransitions(
        ModelHolder $holder,
        ?EnumColumn $sourceState = null,
        bool $visible = false
    ): array {
        return array_filter(
            $this->getMatchingTransitions($sourceState),
            fn(Transition $transition): bool => $transition->canExecute($holder)
                && (!$visible || $transition->isVisible())
        );
    }

    public function getTransitionByTarget(EnumColumn $sourceState, EnumColumn $target): ?Transition
    {
        $candidates = array_filter(
            $this->getMatchingTransitions($sourceState),
            fn(Transition $transition): bool => $transition->target->value == $target->value
        );
        if (count($candidates) == 0) {
            return null;
        } elseif (count($candidates) > 1) {
            throw new InvalidArgumentException(
                sprintf(
                    'Target state %s is from state %s reachable via multiple edges.',
                    $target->value,
                    $sourceState->value
                )
            );
        } else {
            return reset($candidates);
        }
    }

    /**
     * @return Transition[]
     */
    private function getMatchingTransitions(EnumColumn $sourceStateMask): array
    {
        return array_filter(
            $this->transitions,
            fn(Transition $transition): bool => $sourceStateMask->value === $transition->source->value
        );
    }

    /**
     * @param EventParticipantModel $model
     * @throws NeonSchemaException
     */
    public function createHolder(Model $model): ModelHolder
    {
        $holder = $this->eventDispatchFactory->getDummyHolder($model->event);
        $holder->setModel($model);
        return $holder;
    }
}
