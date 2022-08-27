<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Exceptions;

use FKSDB\Models\Events\Machine\Transition;

class TransitionConditionFailedException extends MachineExecutionException
{
    public function __construct(Transition $blockingTransition, int $code = 0, ?\Throwable $previous = null)
    {
        $message = sprintf(
            _("Cannot carry out action '%s' in the automat '%s'."),
            $blockingTransition->getLabel(),
            $blockingTransition->baseMachine->name
        );
        parent::__construct($message, $code, $previous);
    }
}
