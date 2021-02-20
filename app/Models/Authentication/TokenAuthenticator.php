<?php

namespace FKSDB\Models\Authentication;

use FKSDB\Models\Authentication\Exceptions\InactiveLoginException;
use FKSDB\Models\ORM\Models\ModelAuthToken;
use FKSDB\Models\ORM\Models\ModelLogin;
use FKSDB\Models\ORM\Services\ServiceAuthToken;
use FKSDB\Models\ORM\Services\ServiceLogin;
use FKSDB\Models\YearCalculator;
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

    public function __construct(ServiceAuthToken $authTokenService, Session $session, ServiceLogin $serviceLogin, YearCalculator $yearCalculator) {
        parent::__construct($serviceLogin, $yearCalculator);
        $this->authTokenService = $authTokenService;
        $this->session = $session;
    }

    /**
     * @param string $tokenData
     * @return ModelLogin
     * @throws AuthenticationException
     * @throws \Exception
     */
    public function authenticate(string $tokenData): ModelLogin {
        $token = $this->authTokenService->verifyToken($tokenData);
        if (!$token) {
            throw new AuthenticationException(_('Invalid authentication token.'));
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
     * @param string|null $tokenType require specific token type
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
