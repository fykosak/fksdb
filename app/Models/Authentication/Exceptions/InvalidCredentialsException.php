<?php

namespace FKSDB\Models\Authentication\Exceptions;

use Nette\Security\AuthenticationException;
use Nette\Security\Authenticator;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class InvalidCredentialsException extends AuthenticationException {
    public function __construct(?\Throwable $previous = null) {
<<<<<<< HEAD
        parent::__construct(_('Invalid credentials'), IAuthenticator::INVALID_CREDENTIAL, $previous);
=======
        parent::__construct(_('Invalid credentials.'), Authenticator::INVALID_CREDENTIAL, $previous);
>>>>>>> master
    }
}
