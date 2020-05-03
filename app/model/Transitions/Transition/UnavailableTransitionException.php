<?php

namespace FKSDB\Transitions;

use FKSDB\Events\Machine\Transition as EventTransition;
use Nette\InvalidStateException;

/**
 * Class UnavailableTransitionException
 * @package FKSDB\Transitions
 */
class UnavailableTransitionException extends \Exception {
    /**
     * UnavailableTransitionException constructor.
     * @param EventTransition|Transition $transition
     * @param $model
     */
    public function __construct($transition, $model) {
        if ($transition instanceof EventTransition) {
            $source = $transition->getSource();
            $target = $transition->getTarget();
        } elseif ($transition instanceof Transition) {
            $source = $transition->getFromState();
            $target = $transition->getToState();
        } else {
            throw new InvalidStateException;
        }
        parent::__construct(sprintf(
            _('Transition from %s to %s is unavailable for %s'),
            $source,
            $target,
            $model
        ));
    }
}
