<?php

namespace FKSDB\Events;

use FKSDB\Events\Machine\Transition;
use Nette\InvalidArgumentException;
use RuntimeException;
use Traversable;

/**
 * Class MachineExecutionException
 * *
 */
class MachineExecutionException extends RuntimeException {

}

/**
 * Class TransitionConditionFailedException
 * *
 */
class TransitionConditionFailedException extends MachineExecutionException {

    private Transition $transition;

    /**
     * TransitionConditionFailedException constructor.
     * @param Transition $blockingTransition
     * @param null $code
     * @param null $previous
     */
    public function __construct(Transition $blockingTransition, $code = null, $previous = null) {
        $message = sprintf(_("Nelze provést akci '%s' v automatu '%s'."), $blockingTransition->getLabel(), $blockingTransition->getBaseMachine()->getName());
        parent::__construct($message, $code, $previous);
        $this->transition = $blockingTransition;
    }

    /**
     * @return Transition
     */
    public function getTransition() {
        return $this->transition;
    }

}

/**
 * Class TransitionUnsatisfiedTargetException
 * *
 */
class TransitionUnsatisfiedTargetException extends MachineExecutionException {

    /**
     * @var Traversable|array
     */
    private $validationResult;

    /**
     * TransitionUnsatisfiedTargetException constructor.
     * @param $validationResult
     * @param null $code
     * @param null $previous
     */
    public function __construct($validationResult, $code = null, $previous = null) {
        $message = '';
        foreach ($validationResult as $result) {
            $message .= $result;
        }
        parent::__construct($message, $code, $previous);
        $this->validationResult = $validationResult;
    }

    /**
     * @return array|Traversable
     */
    public function getValidationResult() {
        return $this->validationResult;
    }

}

/**
 * Class SubmitProcessingException
 * *
 */
class SubmitProcessingException extends RuntimeException {

}

/**
 * Class TransitionOnExecutedException
 * *
 */
class TransitionOnExecutedException extends MachineExecutionException {

}

/**
 * Class UndeclaredEventException
 * *
 */
class UndeclaredEventException extends InvalidArgumentException {

}
