<?php

namespace FKSDB\Transitions\Transition;

use FKSDB\Events\Machine\Transition as EventTransition;
use FKSDB\ORM\IModel;
use Nette\InvalidStateException;

/**
 * Class UnavailableTransitionException
 * @author Michal Červeňák <miso@fykos.cz>
 */
class UnavailableTransitionException extends \Exception {
    /**
     * UnavailableTransitionException constructor.
     * @param EventTransition|Transition $transition
     * @param IModel $model
     */
    public function __construct($transition, $model) {
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
            $model
        ));
    }
}
