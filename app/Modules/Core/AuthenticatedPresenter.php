<?php

namespace FKSDB\Modules\Core;

use FKSDB\Model\Authentication\GithubAuthenticator;
use FKSDB\Model\Authentication\PasswordAuthenticator;
use FKSDB\Model\Authentication\TokenAuthenticator;
use FKSDB\Model\Authorization\ContestAuthorizator;
use FKSDB\Model\Authorization\EventAuthorizator;
use Exception;
use FKSDB\Modules\CoreModule\AuthenticationPresenter;
use FKSDB\Model\ORM\Models\ModelAuthToken;
use Fykosak\Utils\Logging\Message;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Http\Response;
use ReflectionClass;
use Tracy\Debugger;
use Nette\Http\UserStorage;
use Nette\Security\AuthenticationException;

/**
 * Presenter allows authenticated user access only.
 *
 * User can be authenticated in the session (after successful login)
 * or via an authentication token. It's responsibility of the particular
 * operation to dispose the token after use (if it should be so).
 *
 * @see http://www.php.net/manual/en/features.http-auth.php
 */
abstract class AuthenticatedPresenter extends BasePresenter {
    public const AUTH_ALLOW_LOGIN = 0x1;
    public const AUTH_ALLOW_HTTP = 0x2;
    public const AUTH_ALLOW_TOKEN = 0x4;
    public const AUTH_ALLOW_GITHUB = 0x8;
    protected TokenAuthenticator $tokenAuthenticator;
    protected PasswordAuthenticator $passwordAuthenticator;
    protected GithubAuthenticator $githubAuthenticator;
    protected EventAuthorizator $eventAuthorizator;
    protected ContestAuthorizator $contestAuthorizator;

    final public function injectAuthenticated(
        TokenAuthenticator $tokenAuthenticator,
        PasswordAuthenticator $passwordAuthenticator,
        GithubAuthenticator $githubAuthenticator,
        ContestAuthorizator $contestAuthorizator,
        EventAuthorizator $eventAuthorizator
    ): void {
        $this->tokenAuthenticator = $tokenAuthenticator;
        $this->passwordAuthenticator = $passwordAuthenticator;
        $this->githubAuthenticator = $githubAuthenticator;
        $this->contestAuthorizator = $contestAuthorizator;
        $this->eventAuthorizator = $eventAuthorizator;
    }

    /* Formats action method name.*/
    protected static function formatAuthorizedMethod(string $action): string {
        return 'authorized' . $action;
    }

    /**
     * @param mixed $element
     * @throws ForbiddenRequestException|BadRequestException
     */
    public function checkRequirements($element): void {
        parent::checkRequirements($element);
        if ($element instanceof ReflectionClass) {
            $this->setAuthorized($this->isAuthorized() && $this->getUser()->isLoggedIn());
            if ($this->isAuthorized()) { // check authorization
                $method = $this->formatAuthorizedMethod($this->getAction());
                $this->tryCall($method, $this->getParameters());
            }
        }
    }

    /**
     * @return void
     * @throws AbortException
     * @throws ForbiddenRequestException
     * @throws Exception
     */
    protected function startup(): void {
        parent::startup();

        $methods = $this->getAllowedAuthMethods();

        if ($methods & self::AUTH_ALLOW_TOKEN) {
            // successful token authentication overwrites the user identity (if any)
            $this->tryAuthToken();
        }

        if ($methods & self::AUTH_ALLOW_HTTP) {
            $this->tryHttpAuth();
        }

        if ($methods & self::AUTH_ALLOW_GITHUB) {
            $this->tryGithub();
        }
        // if token did nod succeed redirect to login credentials page
        if (!$this->getUser()->isLoggedIn() && ($methods & self::AUTH_ALLOW_LOGIN)) {
            $this->optionalLoginRedirect();
        } elseif (!$this->isAuthorized()) {
            $this->unauthorizedAccess();
        }
    }

    /**
     * @throws AbortException
     */
    private function optionalLoginRedirect(): void {
        if (!$this->requiresLogin()) {
            return;
        }
        $this->loginRedirect();
    }

    /**
     * @throws AbortException
     */
    final protected function loginRedirect(): void {
        if ($this->user->logoutReason === UserStorage::INACTIVITY) {
            $reason = AuthenticationPresenter::REASON_TIMEOUT;
        } else {
            $reason = AuthenticationPresenter::REASON_AUTH;
        }

        $this->redirect(':Core:Authentication:login', [
            'backlink' => $this->storeRequest(),
            AuthenticationPresenter::PARAM_REASON => $reason,
        ]);
    }

    /**
     * This method may be override, however only simple conditions
     * can be checked there -- user session is not prepared at the
     * moment of the call.
     *
     * @return bool
     */
    public function requiresLogin(): bool {
        return true;
    }

    /**
     * It may be override (should return realm).
     * @return int
     */
    public function getAllowedAuthMethods(): int {
        return self::AUTH_ALLOW_LOGIN | self::AUTH_ALLOW_TOKEN;
    }

    protected function getHttpRealm(): ?string {
        return null;
    }

    /**
     * @throws ForbiddenRequestException
     */
    protected function unauthorizedAccess(): void {
        throw new ForbiddenRequestException();
    }

    /**
     * @throws Exception
     */
    private function tryAuthToken(): void {
        $tokenData = $this->getParameter(TokenAuthenticator::PARAM_AUTH_TOKEN);

        if (!$tokenData) {
            return;
        }

        try {
            $login = $this->tokenAuthenticator->authenticate($tokenData);
            Debugger::log("$login signed in using token $tokenData.", 'token-login');
            if ($this->tokenAuthenticator->isAuthenticatedByToken(ModelAuthToken::TYPE_SSO)) {
                $this->tokenAuthenticator->disposeAuthToken();
            } else {
                $this->flashMessage(_('Successful token authentication.'), Message::LVL_INFO);
            }

            $this->getUser()->login($login);
            $this->redirect('this');
        } catch (AuthenticationException $exception) {
            $this->flashMessage($exception->getMessage(), Message::LVL_ERROR);
        }
    }

    /**
     * @throws BadRequestException
     * @throws Exception
     */
    private function tryHttpAuth(): void {
        if (!isset($_SERVER['PHP_AUTH_USER'])) {
            $this->httpAuthPrompt();
            return;
        }
        try {
            $credentials = [
                PasswordAuthenticator::USERNAME => $_SERVER['PHP_AUTH_USER'],
                PasswordAuthenticator::PASSWORD => $_SERVER['PHP_AUTH_PW'],
            ];
            $login = $this->passwordAuthenticator->authenticate($credentials);

            Debugger::log("$login signed in using HTTP authentication.");

            $this->getUser()->login($login);

            $method = $this->formatAuthorizedMethod($this->getAction());
            $this->tryCall($method, $this->getParameters());
        } catch (AuthenticationException $exception) {
            $this->httpAuthPrompt();
        }
    }

    private function httpAuthPrompt(): void {
        $realm = $this->getHttpRealm();
        if ($realm && $this->requiresLogin()) {
            header('WWW-Authenticate: Basic realm="' . $realm . '"');
            header('HTTP/1.0 401 Unauthorized');
            echo '<h1>Unauthorized</h1>';
            exit;
        }
    }

    /**
     * @throws ForbiddenRequestException|BadRequestException
     * @throws Exception
     */
    private function tryGithub(): void {
        if (!$this->getHttpRequest()->getHeader('X-GitHub-Event')) {
            return;
        }

        try {
            $login = $this->githubAuthenticator->authenticate($this->getHttpRequest());

            Debugger::log("$login signed in using Github authentication.");

            $this->getUser()->login($login);

            $method = $this->formatAuthorizedMethod($this->getAction());
            $this->tryCall($method, $this->getParameters());
        } catch (AuthenticationException $exception) {
            throw new ForbiddenRequestException(_('Authentication failure.'), Response::S403_FORBIDDEN, $exception);
        }
    }
}
