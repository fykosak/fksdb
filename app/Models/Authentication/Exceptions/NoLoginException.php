<?php

namespace FKSDB\Models\Authentication\Exceptions;

use Nette\Security\AuthenticationException;
use Nette\Security\Authenticator;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class NoLoginException extends AuthenticationException {
    public function __construct(?\Throwable $previous = null) {
<<<<<<< HEAD
        parent::__construct(_('Account not prepred.'), IAuthenticator::NOT_APPROVED, $previous);
=======
        parent::__construct(_('Account not ready.'), Authenticator::NOT_APPROVED, $previous);
>>>>>>> master
    }
}
