<?php

namespace Authentication;

use Nette\Security\AuthenticationException;
use Nette\Security\IAuthenticator;
use Nette\Security\IIdentity;
use ServiceLogin;
use ServicePerson;
use YearCalculator;

/**
 * Users authenticator.
 */
class PasswordAuthenticator extends AbstractAuthenticator implements IAuthenticator {

    /**
     * @var ServicePerson
     */
    private $servicePerson;

    function __construct(ServiceLogin $serviceLogin, YearCalculator $yearCalculator, ServicePerson $servicePerson) {
        parent::__construct($serviceLogin, $yearCalculator);
        $this->servicePerson = $servicePerson;
    }

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
        $person = $this->servicePerson->getTable()->where('person_info:email = ?', $id)->fetch();
        $login = null;

        if ($person) {
            $login = $person->getLogin();
            if (!$login) {
                throw new NoLoginException();
            }
        }
        if (!$login) {
            $login = $this->serviceLogin->getTable()->where('login = ?', $id)->fetch();
        }


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
