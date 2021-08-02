<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Exceptions;

class TransitionUnsatisfiedTargetException extends MachineExecutionException
{

    private array $validationResult;

    public function __construct(array $validationResult, int $code = 0, ?\Throwable $previous = null)
    {
        $message = '';
        foreach ($validationResult as $result) {
            $message .= $result;
        }
        parent::__construct($message, $code, $previous);
        $this->validationResult = $validationResult;
    }
}
