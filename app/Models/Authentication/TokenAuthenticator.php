<?php

declare(strict_types=1);

namespace FKSDB\Models\Authentication;

use FKSDB\Models\Authentication\Exceptions\InactiveLoginException;
use FKSDB\Models\ORM\Models\AuthTokenModel;
use FKSDB\Models\ORM\Models\AuthTokenType;
use FKSDB\Models\ORM\Models\LoginModel;
use FKSDB\Models\ORM\Services\AuthTokenService;
use FKSDB\Models\ORM\Services\LoginService;
use Nette\Http\Session;
use Nette\Http\SessionSection;
use Nette\InvalidStateException;
use Nette\Security\AuthenticationException;

class TokenAuthenticator extends AbstractAuthenticator
{

    public const PARAM_AUTH_TOKEN = 'at';
    public const Namespace = 'tokenAuth'; //phpcs:ignore

    private AuthTokenService $authTokenService;
    private SessionSection $section;

    public function __construct(AuthTokenService $authTokenService, Session $session, LoginService $loginService)
    {
        parent::__construct($loginService);
        $this->authTokenService = $authTokenService;
        $this->section = $session->getSection(self::Namespace);
    }

    /**
     * @throws AuthenticationException
     * @throws \Exception
     */
    public function authenticate(string $tokenData): LoginModel
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
