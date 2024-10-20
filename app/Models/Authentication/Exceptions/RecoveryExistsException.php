<?php

declare(strict_types=1);

namespace FKSDB\Models\Authentication\Exceptions;

class RecoveryExistsException extends RecoveryException
{
    public function __construct(?\Throwable $previous = null)
    {
        parent::__construct(_('Account recovery in progress.'), 0, $previous);
    }
}
