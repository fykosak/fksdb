<?php

declare(strict_types=1);

namespace FKSDB\Models\Authentication\Exceptions;

class RecoveryNotImplementedException extends RecoveryException
{
    public function __construct(?\Throwable $previous = null)
    {
        parent::__construct(_('Account cannot be recovered.'), 0, $previous);
    }
}
