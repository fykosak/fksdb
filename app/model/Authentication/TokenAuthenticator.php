<?php

namespace FKSDB\Authentication;

use FKSDB\ORM\Models\ModelAuthToken;
use FKSDB\ORM\Models\ModelLogin;
use FKSDB\ORM\Services\ServiceAuthToken;
use FKSDB\ORM\Services\ServiceLogin;
use FKSDB\YearCalculator;
use Nette\Http\Session;
use Nette\InvalidStateException;
use Nette\Security\AuthenticationException;

/**
 * Users authenticator.
 */
class TokenAuthenticator extends AbstractAuthenticator {

    public const PARAM_AUTH_TOKEN = 'at';
    public const SESSION_NS = 'auth';

    private ServiceAuthToken $authTokenService;

    private Session $session;

    /**
     * TokenAuthenticator constructor.
     * @param ServiceAuthToken $authTokenService
     * @param Session $session
     * @param ServiceLogin $serviceLogin
     * @param YearCalculator $yearCalculator
     */
    public function __construct(ServiceAuthToken $authTokenService, Session $session, ServiceLogin $serviceLogin, YearCalculator $yearCalculator) {
        parent::__construct($serviceLogin, $yearCalculator);
        $this->authTokenService = $authTokenService;
        $this->session = $session;
    }

    /**
     * @param string $tokenData
     * @return ModelLogin
     * @throws AuthenticationException
     */
    public function authenticate($tokenData): ModelLogin {
        $token = $this->authTokenService->verifyToken($tokenData);
        if (!$token) {
            throw new AuthenticationException(_('Autentizační token je neplatný.'));
        }
        // login by the identity
        $login = $token->getLogin();
        if (!$login->active) {
            throw new InactiveLoginException();
        }

        $this->logAuthentication($login);

        $this->storeAuthToken($token);

        return $login;
    }

    /**
     * Get rid off token and user is no more authenticated by the token(?).
     *
     * @return void
     */
    public function disposeAuthToken(): void {
        $section = $this->session->getSection(self::SESSION_NS);
        if (isset($section->token)) {
            $this->authTokenService->disposeToken($section->token);
            $section->remove();
        }
    }

    /**
     * @param string $tokenType require specific token type
     * @return bool true iff user has been authenticated by the authentication token
     */
    public function isAuthenticatedByToken($tokenType = null): bool {
        $section = $this->session->getSection(self::SESSION_NS);
        if (isset($section->token)) {
            return ($tokenType === null) ? true : ($section->type == $tokenType);
        }
        return false;
    }

    /**
     * @return mixed
     */
    public function getTokenData() {
        if (!$this->isAuthenticatedByToken()) {
            throw new InvalidStateException('Not authenticated by token.');
        }
        $section = $this->session->getSection(self::SESSION_NS);
        return $section->data;
    }

    public function disposeTokenData(): void {
        if (!$this->isAuthenticatedByToken()) {
            throw new InvalidStateException('Not authenticated by token.');
        }
        $section = $this->session->getSection(self::SESSION_NS);
        unset($section->data);
    }

    private function storeAuthToken(ModelAuthToken $token): void {
        $section = $this->session->getSection(self::SESSION_NS);
        $section->token = $token->token;
        $section->type = $token->type;
        $section->data = $token->data;
    }

}
