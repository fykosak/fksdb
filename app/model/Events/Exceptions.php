<?php

namespace Events;

use Events\Machine\Transition;
use RuntimeException;

class MachineExecutionException extends RuntimeException {
    
}

class TransitionConditionFailedException extends MachineExecutionException {

    /**
     * @var Transition
     */
    private $transition;

    public function __construct(Transition $blockingTransition, $code = null, $previous = null) {
        $message = sprintf(_("Nelze provést akci '%s' v automatu '%s'."), $blockingTransition->getLabel(), $blockingTransition->getBaseHolder()->getLabel());
        parent::__construct($message, $code, $previous);
        $this->transition = $blockingTransition;
    }

    public function getTransition() {
        return $this->transition;
    }

}

class SubmitProcessingException extends RuntimeException {
    
}

class TransitionOnExecutedException extends MachineExecutionException {
    
}