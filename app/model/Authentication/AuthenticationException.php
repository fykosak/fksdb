<?php

namespace Authentication;

use Nette\Security\AuthenticationException;
use Nette\Security\IAuthenticator;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class InactiveLoginException extends AuthenticationException {

    /**
     * InactiveLoginException constructor.
     * @param null $previous
     */
    public function __construct($previous = null) {
        $message = _('Neaktivní účet.');
        $code = IAuthenticator::NOT_APPROVED;
        parent::__construct($message, $code, $previous);
    }

}

/**
 * Class UnknownLoginException
 * @package Authentication
 */
class UnknownLoginException extends AuthenticationException {

    /**
     * UnknownLoginException constructor.
     * @param null $previous
     */
    public function __construct($previous = null) {
        $message = _('Neexistující účet.');
        $code = IAuthenticator::IDENTITY_NOT_FOUND;
        parent::__construct($message, $code, $previous);
    }

}

/**
 * Class NoLoginException
 * @package Authentication
 */
class NoLoginException extends AuthenticationException {

    /**
     * NoLoginException constructor.
     * @param null $previous
     */
    public function __construct($previous = null) {
        $message = _('Nepřipravený účet.');
        $code = IAuthenticator::NOT_APPROVED;
        parent::__construct($message, $code, $previous);
    }

}

/**
 * Class InvalidCredentialsException
 * @package Authentication
 */
class InvalidCredentialsException extends AuthenticationException {

    /**
     * InvalidCredentialsException constructor.
     * @param null $previous
     */
    public function __construct($previous = null) {
        $message = _('Neplatné přihlašovací údaje.');
        $code = IAuthenticator::INVALID_CREDENTIAL;
        parent::__construct($message, $code, $previous);
    }

}
