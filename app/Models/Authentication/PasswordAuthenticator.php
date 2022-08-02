<?php

declare(strict_types=1);

namespace FKSDB\Models\Authentication;

use FKSDB\Models\Authentication\Exceptions\InactiveLoginException;
use FKSDB\Models\Authentication\Exceptions\InvalidCredentialsException;
use FKSDB\Models\Authentication\Exceptions\NoLoginException;
use FKSDB\Models\Authentication\Exceptions\UnknownLoginException;
use FKSDB\Models\ORM\Models\LoginModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\ServiceLogin;
use FKSDB\Models\ORM\Services\ServicePerson;
use Nette\Security\Authenticator;
use Nette\Security\IdentityHandler;
use Nette\Security\IIdentity;
use Nette\Security\SimpleIdentity;

class PasswordAuthenticator extends AbstractAuthenticator implements Authenticator, IdentityHandler
{
    private ServicePerson $servicePerson;

    public function __construct(ServiceLogin $serviceLogin, ServicePerson $servicePerson)
    {
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
    public function authenticate(string $user, string $password): LoginModel
    {
        $login = $this->findLogin($user);

        if ($login->hash !== $this->calculateHash($password, $login)) {
            throw new InvalidCredentialsException();
        }
        $this->logAuthentication($login);
        return $login;
    }

    public function findPersonByEmail(string $id): ?PersonModel
    {
        return $this->servicePerson->findByEmail($id);
    }

    /**
     * @throws NoLoginException
     */
    private function findByEmail(string $id): ?LoginModel
    {
        $person = $this->servicePerson->findByEmail($id);
        if (!$person) {
            return null;
        }
        $login = $person->getLogin();
        if ($login) {
            return $login;
        }
        throw new NoLoginException();
    }

    /**
     * @throws InactiveLoginException
     * @throws NoLoginException
     * @throws UnknownLoginException
     */
    public function findLogin(string $id): ?LoginModel
    {
        $login = $this->findByEmail($id) ?? $this->findByLogin($id);
        $this->checkLogin($login);
        return $login;
    }

    /**
     * @throws UnknownLoginException
     */
    private function findByLogin(string $id): LoginModel
    {
        /** @var LoginModel $login */
        $login = $this->serviceLogin->getTable()->where('login = ?', $id)->fetch();
        if ($login) {
            return $login;
        }
        throw new UnknownLoginException();
    }

    /**
     * @throws InactiveLoginException
     */
    private function checkLogin(LoginModel $login): void
    {
        if (!$login->active) {
            throw new InactiveLoginException();
        }
    }

    public static function calculateHash(string $password, LoginModel $login): string
    {
        return sha1($login->login_id . md5($password));
    }

    public function sleepIdentity(IIdentity $identity): IIdentity
    {
        if ($identity instanceof LoginModel) {
            $identity = new SimpleIdentity($identity->getId());
        }
        return $identity;
    }

    public function wakeupIdentity(IIdentity $identity): ?LoginModel
    {
        // Find login
        /** @var LoginModel|null $login */
        $login = $this->serviceLogin->findByPrimary($identity->getId());
        return $login;
    }
}
