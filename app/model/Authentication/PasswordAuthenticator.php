<?php

namespace Authentication;

use Nette\Security\AuthenticationException;
use Nette\Security\IAuthenticator;
use Nette\Security\IIdentity;

/**
 * Users authenticator.
 */
class PasswordAuthenticator extends AbstractAuthenticator implements IAuthenticator {

    /**
     * Performs an authentication.
     * @return IIdentity
     * @throws AuthenticationException
     */
    public function authenticate(array $credentials) {
        list($id, $password) = $credentials;

        $login = $this->findLogin($id);

        if ($login->hash !== $this->calculateHash($password, $login)) {
            throw new InvalidCredentialsException();
        }

        $this->logAuthentication($login);

        $login->injectYearCalculator($this->yearCalculator);

        return $login;
    }

    public function findLogin($id) {
        $login = $this->serviceLogin->getTable()->where('login = ? OR email = ?', $id, $id)->fetch();
        if (!$login) {
            throw new UnknownLoginException();
        }

        if (!$login->active) {
            throw new InactiveLoginException();
        }
        return $login;
    }

    /**
     * @param  string
     * @return string
     */
    public static function calculateHash($password, $login) {
        return sha1($login->login_id . md5($password));
    }

}
