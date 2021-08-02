<?php

namespace FKSDB\Models\Authentication\Exceptions;

use Nette\Security\AuthenticationException;
use Nette\Security\Authenticator;

class InactiveLoginException extends AuthenticationException
{
    public function __construct(?\Throwable $previous = null)
    {
        parent::__construct(_('Inactive account.'), Authenticator::NOT_APPROVED, $previous);
    }
}
