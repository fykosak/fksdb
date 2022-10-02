<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Transition;

use Nette\InvalidStateException;

class UnavailableTransitionsException extends InvalidStateException
{

    public function __construct(int $code = 0, \Throwable $previous = null)
    {
        parent::__construct(_('Transition unavailable'), $code, $previous);
    }
}
