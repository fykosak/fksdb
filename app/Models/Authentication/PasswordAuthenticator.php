<?php

declare(strict_types=1);

namespace FKSDB\Models\Authentication;

use FKSDB\Models\Authentication\Exceptions\InactiveLoginException;
use FKSDB\Models\Authentication\Exceptions\InvalidCredentialsException;
use FKSDB\Models\Authentication\Exceptions\UnknownLoginException;
use FKSDB\Models\ORM\Models\LoginModel;
use FKSDB\Models\ORM\Services\LoginService;
use FKSDB\Models\ORM\Services\PersonService;
use Nette\Security\Authenticator;
use Nette\Security\IdentityHandler;
use Nette\Security\IIdentity;
use Nette\Security\SimpleIdentity;

class PasswordAuthenticator extends AbstractAuthenticator implements Authenticator, IdentityHandler
{
    private PersonService $personService;

    public function __construct(LoginService $loginService, PersonService $personService)
    {
        parent::__construct($loginService);
        $this->personService = $personService;
    }

    /**
     * @throws InactiveLoginException
     * @throws InvalidCredentialsException
     * @throws UnknownLoginException
     * @throws \Exception
     */
    public function authenticate(string $user, string $password): LoginModel
    {
        $login = $this->findLogin($user);

        if ($login->hash !== $login->calculateHash($password)) {
            throw new InvalidCredentialsException();
        }
        $this->logAuthentication($login);
        return $login;
    }

    private function findByEmail(string $id): ?LoginModel
    {
        $person = $this->personService->findByEmail($id);
        if (!$person) {
            return null;
        }
        $login = $person->getLogin();
        if ($login) {
            return $login;
        }
        return $this->loginService->createLogin($person);
    }

    /**
     * @throws InactiveLoginException
     * @throws UnknownLoginException
     */
    public function findLogin(string $id): LoginModel
    {
        $login = $this->findByEmail($id) ?? $this->findByLogin($id);
        if (!$login->active) {
            throw new InactiveLoginException();
        }
        return $login;
    }

    /**
     * @throws UnknownLoginException
     */
    private function findByLogin(string $id): LoginModel
    {
        /** @var LoginModel|null $login */
        $login = $this->loginService->getTable()->where('login = ?', $id)->fetch();
        if ($login) {
            return $login;
        }
        throw new UnknownLoginException();
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
        return $this->loginService->findByPrimary($identity->getId());
    }
}
