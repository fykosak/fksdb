<?php

use Nette\DateTime;
use Nette\Object;
use Nette\Security\AuthenticationException;
use Nette\Security\IAuthenticator;
use Nette\Security\IIdentity;

/**
 * Users authenticator.
 */
class Authenticator extends Object implements IAuthenticator {

    const HASHED_PASSWORD = 10;

    /** @var ServiceLogin */
    private $serviceLogin;

    /** @var YearCalculator */
    private $yearCalculator;

    public function __construct(ServiceLogin $serviceLogin, YearCalculator $yc) {
        $this->serviceLogin = $serviceLogin;
        $this->yearCalculator = $yc;
    }

    /**
     * Performs an authentication.
     * @return IIdentity
     * @throws AuthenticationException
     */
    public function authenticate(array $credentials) {
        list($id, $password) = $credentials;

        $login = $this->serviceLogin->getTable()->where('login = ? OR email = ?', $id, $id)->where('active = 1')->fetch();

        if (!$login) {
            throw new AuthenticationException('Neplatné přihlašovací údaje.', self::INVALID_CREDENTIAL);
        }

        if ($login->hash !== $this->calculateHash($password, $login)) {
            throw new AuthenticationException('Neplatné přihlašovací údaje.', self::INVALID_CREDENTIAL);
        }

        $login->last_login = DateTime::from(time());
        $this->serviceLogin->save($login);
        $login->injectYearCalculator($this->yearCalculator);

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
