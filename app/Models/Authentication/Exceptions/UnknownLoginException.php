<?php

declare(strict_types=1);

namespace FKSDB\Models\Authentication\Exceptions;

use Nette\Security\AuthenticationException;
use Nette\Security\Authenticator;

class UnknownLoginException extends AuthenticationException {
    public function __construct(?\Throwable $previous = null) {
        parent::__construct(_('Unknown account.'), Authenticator::IDENTITY_NOT_FOUND, $previous);
    }
}
