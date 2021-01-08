<?php

namespace FKSDB\Models\Authentication\Exceptions;

use Nette\Security\AuthenticationException;
use Nette\Security\IAuthenticator;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class InvalidCredentialsException extends AuthenticationException {
    public function __construct(?\Throwable $previous = null) {
        parent::__construct(_('Neplatné přihlašovací údaje.'), IAuthenticator::INVALID_CREDENTIAL, $previous);
    }
}
