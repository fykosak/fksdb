<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Transition;

use Nette\InvalidStateException;

class UnavailableTransitionsException extends InvalidStateException
{
    public const ReasonNone = 1;// phpcs:ignore
    public const ReasonLot = 2;// phpcs:ignore

    public function __construct(int $code = 0, \Throwable $previous = null)
    {
        parent::__construct(_('Transition unavailable'), $code, $previous);
    }
}
