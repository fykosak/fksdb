<?php

namespace FKSDB\Models\Transitions\Transition;


use FKSDB\Models\Events\Machine\Transition as EventTransition;
use FKSDB\Models\Transitions\Holder\IModelHolder;
use Nette\InvalidStateException;

/**
 * Class UnavailableTransitionException
 * @author Michal Červeňák <miso@fykos.cz>
 */
class UnavailableTransitionException extends \Exception {

    /**
     * UnavailableTransitionException constructor.
     * @param EventTransition|Transition $transition
     * @param IModelHolder|null $holder
     */
    public function __construct($transition, ?IModelHolder $holder) {
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
            $holder->getModel()
        ));
    }
}
