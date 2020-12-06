<?php

namespace FKSDB\Events\Exceptions;

class TransitionUnsatisfiedTargetException extends MachineExecutionException {

    /** @var iterable */
    private $validationResult;

    /**
     * TransitionUnsatisfiedTargetException constructor.
     * @param mixed $validationResult
     * @param int $code
     * @param \Throwable|null $previous
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
