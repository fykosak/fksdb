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
     * @param ActiveRow|ModelHolder|null $holder
     */
    public function __construct(Transition $transition, $holder)
    {
        if ($transition instanceof EventTransition) {
            $source = $transition->getSource();
            $target = $transition->target;
        } else {
            $source = $transition->sourceStateEnum->value;
            $target = $transition->targetStateEnum->value;
        }
        parent::__construct(
            sprintf(
                _('Transition from %s to %s is unavailable for %s'),
                $source,
                $target,
                $holder instanceof ModelHolder ? $holder->getModel() : $holder
            )
        );
    }
}
