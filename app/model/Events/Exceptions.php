<?php

namespace FKSDB\Events;

use FKSDB\Events\Machine\Transition;
use Nette\InvalidArgumentException;
use RuntimeException;

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

    public function __construct(Transition $blockingTransition, int $code = 0, ?\Throwable $previous = null) {
        $message = sprintf(_("Nelze provést akci '%s' v automatu '%s'."), $blockingTransition->getLabel(), $blockingTransition->getBaseMachine()->getName());
        parent::__construct($message, $code, $previous);
        $this->transition = $blockingTransition;
    }

    public function getTransition(): Transition {
        return $this->transition;
    }
}

/**
 * Class TransitionUnsatisfiedTargetException
 * *
 */
class TransitionUnsatisfiedTargetException extends MachineExecutionException {

    /** @var iterable */
    private $validationResult;

    /**
     * TransitionUnsatisfiedTargetException constructor.
     * @param mixed $validationResult
     * @param null $code
     * @param null $previous
     */
    public function __construct($validationResult, int $code = 0, ?\Throwable $previous = null) {
        $message = '';
        foreach ($validationResult as $result) {
            $message .= $result;
        }
        parent::__construct($message, $code, $previous);
        $this->validationResult = $validationResult;
    }

    /**
     * @return iterable
     */
    public function getValidationResult() {
        return $this->validationResult;
    }

}

class SubmitProcessingException extends RuntimeException {
}

class TransitionOnExecutedException extends MachineExecutionException {
}

class UndeclaredEventException extends InvalidArgumentException {
}
