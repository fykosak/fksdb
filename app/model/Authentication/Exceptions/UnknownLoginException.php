<?php

namespace FKSDB\Authentication;

use Nette\Security\AuthenticationException;
use Nette\Security\IAuthenticator;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class UnknownLoginException extends AuthenticationException {
    public function __construct(?\Throwable $previous = null) {
        parent::__construct(_('Neexistující účet.'), IAuthenticator::IDENTITY_NOT_FOUND, $previous);
    }
}
