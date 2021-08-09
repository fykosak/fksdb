<?php

declare(strict_types=1);

namespace FKSDB\Models\Authentication\Exceptions;

use Nette\Security\AuthenticationException;
use Nette\Security\Authenticator;

class NoLoginException extends AuthenticationException
{
    public function __construct(?\Throwable $previous = null)
    {
        parent::__construct(_('Account not ready.'), Authenticator::NOT_APPROVED, $previous);

    }
}
