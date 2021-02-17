<?php

namespace FKSDB\Models\Events\Exceptions;

use FKSDB\Models\Events\Machine\Transition;

class TransitionConditionFailedException extends MachineExecutionException {

    private Transition $transition;

    public function __construct(Transition $blockingTransition, int $code = 0, ?\Throwable $previous = null) {
        $message = sprintf(_("Nelze provést akci '%s' v automatu '%s'."), $blockingTransition->getLabel(), $blockingTransition->getBaseMachine()->getName());
        parent::__construct($message, $code, $previous);
        $this->transition = $blockingTransition;
    }
}
