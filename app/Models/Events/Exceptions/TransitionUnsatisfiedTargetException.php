<?php

namespace FKSDB\Models\Events\Exceptions;

class TransitionUnsatisfiedTargetException extends MachineExecutionException {

    private array $validationResult;

    public function __construct(array $validationResult, int $code = 0, ?\Throwable $previous = null) {
        parent::__construct(implode('', $validationResult), $code, $previous);
        $this->validationResult = $validationResult;
    }
}
