<?php

declare(strict_types=1);

namespace FKSDB\Models\Authentication\Exceptions;

use Nette\Security\AuthenticationException;
use Nette\Security\IAuthenticator;

class InvalidCredentialsException extends AuthenticationException
{
    public function __construct(?\Throwable $previous = null)
    {
        parent::__construct(_('Invalid credentials.'), IAuthenticator::INVALID_CREDENTIAL, $previous);
    }
}
