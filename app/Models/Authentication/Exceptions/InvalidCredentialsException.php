<?php

namespace FKSDB\Models\Authentication\Exceptions;

use Nette\Security\AuthenticationException;
use Nette\Security\Authenticator;

class InvalidCredentialsException extends AuthenticationException
{
    public function __construct(?\Throwable $previous = null)
    {
        parent::__construct(_('Invalid credentials.'), Authenticator::INVALID_CREDENTIAL, $previous);
    }
}
