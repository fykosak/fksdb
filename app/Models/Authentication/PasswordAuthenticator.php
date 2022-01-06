<?php

declare(strict_types=1);

namespace FKSDB\Models\Authentication;

use FKSDB\Models\Authentication\Exceptions\InactiveLoginException;
use FKSDB\Models\Authentication\Exceptions\InvalidCredentialsException;
use FKSDB\Models\Authentication\Exceptions\NoLoginException;
use FKSDB\Models\Authentication\Exceptions\UnknownLoginException;
use FKSDB\Models\ORM\Models\ModelLogin;
use FKSDB\Models\ORM\Models\ModelPerson;
use FKSDB\Models\ORM\Services\ServiceLogin;
use FKSDB\Models\ORM\Services\ServicePerson;
use Nette\Security\Authenticator;
use Nette\Security\IdentityHandler;
use Nette\Security\IIdentity;
use Nette\Security\SimpleIdentity;

class PasswordAuthenticator extends AbstractAuthenticator implements Authenticator, IdentityHandler {

    private ServicePerson $servicePerson;

    public function __construct(ServiceLogin $serviceLogin, ServicePerson $servicePerson) {
        parent::__construct($serviceLogin);
        $this->servicePerson = $servicePerson;
    }

    /**
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
     * @param ModelLogin|object $login
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

    public function wakeupIdentity(IIdentity $identity): ?ModelLogin {
        // Find login
        /** @var ModelLogin|null $login */
        $login = $this->serviceLogin->findByPrimary($identity->getId());
        return $login;
    }
}
