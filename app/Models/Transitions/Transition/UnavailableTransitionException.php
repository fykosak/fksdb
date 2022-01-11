<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Transition;

use FKSDB\Models\Events\Machine\Transition as EventTransition;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use Nette\Database\Table\ActiveRow;
use Nette\InvalidStateException;

class UnavailableTransitionException extends InvalidStateException
{

    /**
     * UnavailableTransitionException constructor.
     * @param EventTransition|Transition $transition
     * @param ActiveRow|ModelHolder|null $holder
     */
    public function __construct($transition, $holder)
    {
        if ($transition instanceof EventTransition) {
            $source = $transition->getSource();
        } elseif ($transition instanceof Transition) {
            $source = $transition->sourceState;
        } else {
            throw new InvalidStateException();
        }
        parent::__construct(
            sprintf(
                _('Transition from %s to %s is unavailable for %s'),
                $source,
                $transition->targetState,
                $holder instanceof ModelHolder ? $holder->getModel() : $holder
            )
        );
    }
}
