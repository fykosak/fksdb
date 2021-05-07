<?php

namespace FKSDB\Models\Authentication;

use FKSDB\Models\Authentication\Exceptions\InactiveLoginException;
use FKSDB\Models\Authentication\Exceptions\InvalidCredentialsException;
use FKSDB\Models\Authentication\Exceptions\NoLoginException;
use FKSDB\Models\Authentication\Exceptions\UnknownLoginException;
use FKSDB\Models\Authentication\SSO\GlobalSession;
use FKSDB\Models\ORM\Models\ModelLogin;
use FKSDB\Models\ORM\Models\ModelPerson;
use FKSDB\Models\ORM\Services\ServiceLogin;
use FKSDB\Models\ORM\Services\ServicePerson;
use Nette\Security\Authenticator;
use Nette\Security\IdentityHandler;
use Nette\Security\IIdentity;
use Nette\Security\SimpleIdentity;

/**
 * Users authenticator.
 */
class PasswordAuthenticator extends AbstractAuthenticator implements Authenticator, IdentityHandler {

    private ServicePerson $servicePerson;
    private GlobalSession $globalSession;

    public function __construct(ServiceLogin $serviceLogin, ServicePerson $servicePerson, GlobalSession $globalSession) {
        parent::__construct($serviceLogin);
        $this->servicePerson = $servicePerson;
        $this->globalSession = $globalSession;
    }

    /**
     * Performs an authentication.
     * @param string $user
     * @param string $password
     * @return ModelLogin
     * @throws InactiveLoginException
     * @throws InvalidCredentialsException
     * @throws NoLoginException
     * @throws UnknownLoginException
     * @throws \Exception
     */
    public function authenticate(string $user, string $password): ModelLogin {
        $login = $this->findLogin($user);

        if ($login->hash !== $this->calculateHash($password, $login)) {
            throw new InvalidCredentialsException();
        }

        $this->logAuthentication($login);

        return $login;
    }

    /**
     * @param string $id
     * @return ModelLogin
     * @throws InactiveLoginException
     * @throws NoLoginException
     * @throws UnknownLoginException
     */
    public function findLogin(string $id): ModelLogin {
        /** @var ModelPerson $person */
        $person = $this->servicePerson->getTable()->where(':person_info.email = ?', $id)->fetch();
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
     * @param string $password
     * @param ModelLogin|object $login
     * @return string
     */
    public static function calculateHash(string $password, $login): string {
        return sha1($login->login_id . md5($password));
    }

    public function sleepIdentity(IIdentity $identity): IIdentity {
        if ($identity instanceof ModelLogin) {
            $identity = new SimpleIdentity($identity->getId());
        }
        return $identity;
    }

    public function wakeupIdentity(IIdentity $identity): ?IIdentity {
        $global = $this->globalSession->getUIdSession();
        if ($global) {
            // update identity
            return $global->getLogin();
        }

        // Find login
        /** @var ModelLogin $login */
        $login = $this->serviceLogin->findByPrimary($identity->getId());

        if (!$login) {
            return null;
        }
        return $login;
    }
}
