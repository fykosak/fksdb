<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms\Fyziklani;

use Nette\InvalidStateException;
use Throwable;

class NoMemberException extends InvalidStateException
{
    public function __construct(int $code = 0, Throwable $previous = null)
    {
        parent::__construct(_('Application must have atleast one member'), $code, $previous);
    }
}
