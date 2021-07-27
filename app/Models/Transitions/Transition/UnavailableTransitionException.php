<?php

namespace FKSDB\Models\Transitions\Transition;

use FKSDB\Models\Events\Machine\Transition as EventTransition;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use Nette\Database\Table\ActiveRow;
use Nette\InvalidStateException;

class UnavailableTransitionException extends \Exception {

    /**
     * UnavailableTransitionException constructor.
     * @param EventTransition|Transition $transition
     * @param ActiveRow|ModelHolder|null $holder
     */
    public function __construct($transition, $holder) {
        $target = $transition->getTargetState();
        if ($transition instanceof EventTransition) {
            $source = $transition->getSource();
        } elseif ($transition instanceof Transition) {
            $source = $transition->getSourceState();
        } else {
            throw new InvalidStateException();
        }
        parent::__construct(sprintf(
            _('Transition from %s to %s is unavailable for %s'),
            $source,
            $target,
            $holder instanceof ModelHolder ? $holder->getModel() : $holder
        ));
    }
}
