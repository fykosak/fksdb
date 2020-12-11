<?php

namespace FKSDB\Model\Authentication\Exceptions;

use Nette\Security\AuthenticationException;
use Nette\Security\IAuthenticator;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class NoLoginException extends AuthenticationException {
    public function __construct(?\Throwable $previous = null) {
        parent::__construct(_('Account not ready.'), IAuthenticator::NOT_APPROVED, $previous);
    }
}
