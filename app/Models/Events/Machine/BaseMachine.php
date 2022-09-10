<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Machine;

use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\Transitions\Machine\AbstractMachine;
use Nette\InvalidArgumentException;

/**
 * @property Transition[] $transitions
 */
class BaseMachine extends AbstractMachine
{

    public string $name = 'participant';

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
        BaseHolder $holder,
        EnumColumn $sourceState,
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
            fn(Transition $transition): bool => $transition->matchSource($sourceStateMask)
        );
    }
}
