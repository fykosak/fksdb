<?php

/**
 * Users authenticator.
 */
class Authenticator extends NObject implements IAuthenticator {

    /** @var ServiceLogin */
    private $serviceLogin;

    public function __construct(ServiceLogin $serviceLogin) {
        $this->serviceLogin = $serviceLogin;
    }

    /**
     * Performs an authentication.
     * @return NIdentity
     * @throws NAuthenticationException
     */
    public function authenticate(array $credentials) {
        list($id, $password) = $credentials;

        $login = $this->serviceLogin->getTable()->where('login = ? OR email = ?', $id, $id)->where('active = 1')->fetch();

        if (!$login) {
            throw new NAuthenticationException('Neplatné přihlašovací údaje.', self::INVALID_CREDENTIAL);
        }

        if ($login->hash !== $this->calculateHash($password, $login)) {
            throw new NAuthenticationException('Neplatné přihlašovací údaje.', self::INVALID_CREDENTIAL);
        }

        $login->last_login = NDateTime53::from(time());
        $this->serviceLogin->save($login);

        return $login->getPerson();
    }

    /**
     * @param  string
     * @return string
     */
    public static function calculateHash($password, $login) {
        return sha1($password); //TODO
    }

}
