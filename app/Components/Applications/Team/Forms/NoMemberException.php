<?php

declare(strict_types=1);

namespace FKSDB\Components\Applications\Team\Forms;

use Nette\InvalidStateException;

class NoMemberException extends InvalidStateException
{
    public function __construct(int $code = 0, \Throwable $previous = null)
    {
        parent::__construct(_('Application must have at least one member'), $code, $previous);
    }
}
