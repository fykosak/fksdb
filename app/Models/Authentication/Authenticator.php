<?php

declare(strict_types=1);

namespace FKSDB\Models\Authentication;

use FKSDB\Models\Authentication\Exceptions\InactiveLoginException;
use FKSDB\Models\Authentication\Exceptions\InvalidCredentialsException;
use FKSDB\Models\Authentication\Exceptions\UnknownLoginException;
use FKSDB\Models\ORM\Models\AuthTokenModel;
use FKSDB\Models\ORM\Models\AuthTokenType;
use FKSDB\Models\ORM\Models\ContestModel;
use FKSDB\Models\ORM\Models\LoginModel;
use FKSDB\Models\ORM\Models\OrganizerModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\AuthTokenService;
use FKSDB\Models\ORM\Services\LoginService;
use FKSDB\Models\ORM\Services\OrganizerService;
use FKSDB\Models\ORM\Services\PersonService;
use Nette\Http\Session;
use Nette\Http\SessionSection;
use Nette\InvalidStateException;
use Nette\Security\AuthenticationException;
use Nette\Security\IdentityHandler;
use Nette\Security\IIdentity;
use Nette\Security\SimpleIdentity;
use Nette\Utils\DateTime;
use Tracy\Debugger;

/**
 * @note IAuthenticator interface is not explicitly implemented due to 'array'
 * type hint at authenticate method.
 */
final class Authenticator implements \Nette\Security\Authenticator, IdentityHandler
{
    public const PARAM_AUTH_TOKEN = 'at';
    public const Namespace = 'tokenAuth';//phscs:ignore

    private OrganizerService $organizerService;
    private PersonService $personService;
    protected LoginService $loginService;
    private AuthTokenService $authTokenService;
    private SessionSection $section;

//phpcs:ignore

    public function __construct(
        AuthTokenService $authTokenService,
        Session $session,
        OrganizerService $organizerService,
        LoginService $loginService,
        PersonService $personService
    ) {
        $this->loginService = $loginService;
        $this->organizerService = $organizerService;
        $this->personService = $personService;
        $this->authTokenService = $authTokenService;
        $this->section = $session->getSection(self::Namespace);
    }

    /**
     * @throws InactiveLoginException
     * @throws InvalidCredentialsException
     * @throws UnknownLoginException
     * @throws \Exception
     */
    public function authenticatePassword(string $user, string $password): LoginModel
    {
        $login = $this->findLogin($user);

        if ($login->hash !== $login->calculateHash($password)) {
            throw new InvalidCredentialsException();
        }
        $this->logAuthentication($login);
        return $login;
    }

    /**
     * @throws UnknownLoginException
     * @throws AuthenticationException
     * @throws InactiveLoginException
     * @throws \Exception
     * @phpstan-param array{email:string|null} $user
     */
    public function authenticateGoogle(array $user): LoginModel
    {
        $person = $this->findPerson($user);

        if (!$person) {
            throw new UnknownLoginException();
        } else {
            $login = $person->getLogin();
            if (!$login) {
                $login = $this->loginService->createLogin($person);
            }
        }
        if ($login->active == 0) {
            throw new InactiveLoginException();
        }
        $this->logAuthentication($login);
        return $login;
    }

    /**
     * @throws AuthenticationException
     * @throws \Exception
     */
    public function authenticateToken(string $tokenData): LoginModel
    {
        $token = $this->authTokenService->findToken($tokenData);
        if (!$token || !$token->isActive()) {
            throw new AuthenticationException(_('Invalid authentication token.'));
        }
        // login by the identity
        $login = $token->login;
        if (!$login->active) {
            throw new InactiveLoginException();
        }

        $this->logAuthentication($login);

        $this->storeAuthToken($token);

        return $login;
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

    public function authenticate(string $user, string $password): LoginModel
    {
        return $this->authenticatePassword($user, $password);
    }

    /**
     * Get rid off token and user is no more authenticated by the token(?).
     */
    public function disposeAuthToken(): void
    {
        if (isset($this->section->token)) {
            $this->authTokenService->disposeToken($this->section->token);
            $this->section->remove();
        }
    }

    public function isAuthenticatedByToken(?AuthTokenType $tokenType = null): bool
    {
        if (isset($this->section->token)) {
            return $tokenType === null || $this->section->type == $tokenType->value;
        }
        return false;
    }

    public function getTokenData(): ?string
    {
        if (!$this->isAuthenticatedByToken()) {
            throw new InvalidStateException(_('Not authenticated by token.'));
        }
        return $this->section->data;
    }

    private function storeAuthToken(AuthTokenModel $token): void
    {
        $this->section->token = $token->token;
        $this->section->type = $token->type;
        $this->section->data = $token->data;
    }

    /**
     * @throws \Exception
     */
    private function logAuthentication(LoginModel $login): void
    {
        Debugger::log(
            sprintf('LoginId %s (%s) successfully logged in', $login->login_id, $login->person),
            'auth'
        );
        $this->loginService->storeModel(['last_login' => DateTime::from(time())], $login);
    }

    /**
     * @throws AuthenticationException
     * @phpstan-param array{email:string|null} $user
     */
    private function findPerson(array $user): ?PersonModel
    {
        if (!$user['email']) {
            throw new AuthenticationException(_('Email not found in the google account.'));
        }
        return $this->findOrganizer($user) ?? $this->personService->findByEmail($user['email']);
    }

    /**
     * @phpstan-param array{email:string|null} $user
     */
    private function findOrganizer(array $user): ?PersonModel
    {
        [$domainAlias, $domain] = explode('@', $user['email']);
        switch ($domain) {
            case 'fykos.cz':
                $contestId = ContestModel::ID_FYKOS;
                break;
            case 'vyfuk.org':
                $contestId = ContestModel::ID_VYFUK;
                break;
            default:
                return null;
        }
        /** @var OrganizerModel|null $organizers */
        $organizers = $this->organizerService->getTable()
            ->where(['domain_alias' => $domainAlias, 'contest_id' => $contestId])
            ->fetch();
        return $organizers ? $organizers->person : null;
    }


    private function findByEmail(string $email): ?LoginModel
    {
        $person = $this->personService->findByEmail($email);
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
    public function findLogin(string $login): LoginModel
    {
        $login = $this->findByEmail($login) ?? $this->findByLogin($login);
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

}
