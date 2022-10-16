<?php

declare(strict_types=1);

namespace FKSDB\Models\Authentication;

use FKSDB\Models\Authentication\Exceptions\InactiveLoginException;
use FKSDB\Models\ORM\Models\AuthTokenModel;
use FKSDB\Models\ORM\Models\LoginModel;
use FKSDB\Models\ORM\Services\AuthTokenService;
use FKSDB\Models\ORM\Services\LoginService;
use Nette\Http\Session;
use Nette\InvalidStateException;
use Nette\Security\AuthenticationException;

class TokenAuthenticator extends AbstractAuthenticator
{
    public const PARAM_AUTH_TOKEN = 'at';
    public const SESSION_NS = 'auth';

    private AuthTokenService $authTokenService;
    private Session $session;

    public function __construct(AuthTokenService $authTokenService, Session $session, LoginService $loginService)
    {
        parent::__construct($loginService);
        $this->authTokenService = $authTokenService;
        $this->session = $session;
    }

    /**
     * @throws AuthenticationException
     * @throws \Exception
     */
    public function authenticate(string $tokenData): LoginModel
    {
        $token = $this->authTokenService->verifyToken($tokenData);
        if (!$token) {
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

    /**
     * Get rid off token and user is no more authenticated by the token(?).
     */
    public function disposeAuthToken(): void
    {
        $section = $this->session->getSection(self::SESSION_NS);
        if (isset($section->token)) {
            $this->authTokenService->disposeToken($section->token);
            $section->remove();
        }
    }

    /**
     * @param string|null $tokenType require specific token type
     * @return bool true iff user has been authenticated by the authentication token
     */
    public function isAuthenticatedByToken(?string $tokenType = null): bool
    {
        $section = $this->session->getSection(self::SESSION_NS);
        if (isset($section->token)) {
            return $tokenType === null || $section->type == $tokenType;
        }
        return false;
    }

    public function getTokenData(): ?string
    {
        if (!$this->isAuthenticatedByToken()) {
            throw new InvalidStateException('Not authenticated by token.');
        }
        $section = $this->session->getSection(self::SESSION_NS);
        return $section->data;
    }

    public function disposeTokenData(): void
    {
        if (!$this->isAuthenticatedByToken()) {
            throw new InvalidStateException('Not authenticated by token.');
        }
        $section = $this->session->getSection(self::SESSION_NS);
        unset($section->data);
    }

    private function storeAuthToken(AuthTokenModel $token): void
    {
        $section = $this->session->getSection(self::SESSION_NS);
        $section->token = $token->token;
        $section->type = $token->type;
        $section->data = $token->data;
    }
}
