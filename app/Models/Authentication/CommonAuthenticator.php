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
use FKSDB\Models\ORM\Services\AuthTokenService;
use FKSDB\Models\ORM\Services\LoginService;
use FKSDB\Models\ORM\Services\OrganizerService;
use FKSDB\Models\ORM\Services\PersonService;
use Nette\Http\Session;
use Nette\Http\SessionSection;
use Nette\InvalidStateException;
use Nette\Security\AuthenticationException;
use Nette\Security\Authenticator;
use Nette\Security\IdentityHandler;
use Nette\Security\IIdentity;
use Nette\Security\SimpleIdentity;
use Nette\Utils\DateTime;
use Tracy\Debugger;

class CommonAuthenticator implements Authenticator, IdentityHandler
{
    public const PARAM_AUTH_TOKEN = 'at';
    public const Namespace = 'tokenAuth'; //phpcs:ignore

    private AuthTokenService $authTokenService;
    private SessionSection $section;
    private LoginService $loginService;
    private OrganizerService $organizerService;
    private PersonService $personService;

    public function __construct(
        OrganizerService $organizerService,
        LoginService $loginService,
        PersonService $personService,
        AuthTokenService $authTokenService,
        Session $session
    ) {
        $this->organizerService = $organizerService;
        $this->personService = $personService;
        $this->loginService = $loginService;
        $this->authTokenService = $authTokenService;
        $this->section = $session->getSection(self::Namespace);
    }

    public function authenticate(string $user, string $password): IIdentity
    {
        return $this->authenticatePassword($user, $password);
    }

    /**
     * @throws InactiveLoginException
     * @throws InvalidCredentialsException
     * @throws UnknownLoginException
     * @throws \Exception
     */
    public function authenticatePassword(string $user, string $password): LoginModel
    {
        $login = $this->findByLogin($user);

        if ($login->hash !== $login->calculateHash($password)) {
            throw new InvalidCredentialsException();
        }
        $this->log($login);
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
        if (!$user['email']) {
            throw new AuthenticationException(_('Email not found in the google account.'));
        }
        $login = $this->findByEmail($user['email']);

        $this->log($login);
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
        $this->log($login);
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

    /**
     * @throws \Exception
     */
    private function log(LoginModel $login): void
    {
        Debugger::log(
            sprintf('LoginId %s (%s) successfully logged in', $login->login_id, $login->person),
            'auth'
        );
        $this->loginService->storeModel(['last_login' => DateTime::from(time())], $login);
    }

    /**
     * @throws InactiveLoginException
     * @throws UnknownLoginException
     */
    private function findByEmail(string $email): LoginModel
    {
        $login = $this->innerFindByContestEmail($email) ?? $this->innerFindByPersonEmail($email);
        if (!$login) {
            throw new UnknownLoginException();
        }
        if (!$login->active) {
            throw new InactiveLoginException();
        }
        return $login;
    }

    /**
     * @throws InactiveLoginException
     * @throws UnknownLoginException
     */
    public function findByLogin(string $id): LoginModel
    {
        $login = $this->innerFindByContestEmail($id)
            ?? $this->innerFindByPersonEmail($id)
            ?? $this->innerFindLogin($id);
        if (!$login) {
            throw new UnknownLoginException();
        }
        if (!$login->active) {
            throw new InactiveLoginException();
        }
        return $login;
    }

    private function innerFindByContestEmail(string $email): ?LoginModel
    {
        [$domainAlias, $domain] = explode('@', $email);
        switch ($domain) {
            case 'fykos.cz':
            case 'fykos.org':
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
        return $organizers ? $organizers->person->getLogin() : null;
    }

    private function innerFindByPersonEmail(string $email): ?LoginModel
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

    private function innerFindLogin(string $id): ?LoginModel
    {
        /** @var LoginModel|null $login */
        $login = $this->loginService->getTable()->where('login', $id)->fetch();
        return $login;
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
}
