<?php

declare(strict_types=1);

namespace FKSDB\Modules\Core;

use FKSDB\Models\Authentication\GithubAuthenticator;
use FKSDB\Models\Authentication\PasswordAuthenticator;
use FKSDB\Models\Authentication\TokenAuthenticator;
use FKSDB\Models\Authorization\ContestAuthorizator;
use FKSDB\Models\Authorization\EventAuthorizator;
use FKSDB\Modules\CoreModule\AuthenticationPresenter;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Http\Response;
use Tracy\Debugger;
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
abstract class AuthenticatedPresenter extends BasePresenter
{

    public const AUTH_LOGIN = 'login';
    public const AUTH_HTTP = 'http';
    public const AUTH_TOKEN = 'token';
    public const AUTH_GITHUB = 'github';

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
    protected static function formatAuthorizedMethod(string $action): string
    {
        return 'authorized' . $action;
    }

    /**
     * @param mixed $element
     * @throws BadRequestException
     */
    public function checkRequirements($element): void
    {
        parent::checkRequirements($element);
        if ($element instanceof \ReflectionClass) {
            $this->setAuthorized($this->isAuthorized() && $this->getUser()->isLoggedIn());
            if ($this->isAuthorized()) { // check authorization
                $method = $this->formatAuthorizedMethod($this->getAction());
                $this->tryCall($method, $this->getParameters());
            }
        }
    }

    /**
     * @return void
     * @throws ForbiddenRequestException
     * @throws \Exception
     */
    protected function startup(): void
    {
        parent::startup();

        $methods = $this->getAllowedAuthMethods();

        if ($methods[self::AUTH_TOKEN]) {
            // successful token authentication overwrites the user identity (if any)
            $this->tryAuthToken();
        }

        if ($methods[self::AUTH_HTTP]) {
            $this->tryHttpAuth();
        }

        if ($methods[self::AUTH_GITHUB]) {
            $this->tryGithub();
        }
        // if token did nod succeed redirect to login credentials page
        if (!$this->getUser()->isLoggedIn() && ($methods[self::AUTH_LOGIN])) {
            $this->optionalLoginRedirect();
        } elseif (!$this->isAuthorized()) {
            $this->unauthorizedAccess();
        }
    }

    private function optionalLoginRedirect(): void
    {
        if (!$this->requiresLogin()) {
            return;
        }
        $this->redirect(':Core:Authentication:login', [
            'backlink' => $this->storeRequest(),
            AuthenticationPresenter::PARAM_REASON => $this->getUser()->logoutReason,
        ]);
    }

    /**
     * This method may be override, however only simple conditions
     * can be checked there -- user session is not prepared at the
     * moment of the call.
     *
     * @return bool
     */
    public function requiresLogin(): bool
    {
        return true;
    }

    public function getAllowedAuthMethods(): array
    {
        return [
            self::AUTH_GITHUB => false,
            self::AUTH_HTTP => false,
            self::AUTH_LOGIN => true,
            self::AUTH_TOKEN => true,
        ];
    }

    protected function getHttpRealm(): ?string
    {
        return null;
    }

    /**
     * @throws ForbiddenRequestException
     */
    protected function unauthorizedAccess(): void
    {
        throw new ForbiddenRequestException();
    }

    /**
     * @throws \Exception
     */
    private function tryAuthToken(): void
    {
        $tokenData = $this->getParameter(TokenAuthenticator::PARAM_AUTH_TOKEN);

        if (!$tokenData) {
            return;
        }

        try {
            $login = $this->tokenAuthenticator->authenticate($tokenData);
            Debugger::log("$login signed in using token $tokenData.", 'token-login');
            $this->flashMessage(_('Successful token authentication.'), self::FLASH_INFO);

            $this->getUser()->login($login);
            $this->redirect('this');
        } catch (AuthenticationException $exception) {
            $this->flashMessage($exception->getMessage(), self::FLASH_ERROR);
        }
    }

    /**
     * @throws BadRequestException
     * @throws \Exception
     */
    private function tryHttpAuth(): void
    {
        if (!isset($_SERVER['PHP_AUTH_USER'])) {
            $this->httpAuthPrompt();
            return;
        }
        try {
            $login = $this->passwordAuthenticator->authenticate($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);

            Debugger::log("$login signed in using HTTP authentication.");

            $this->getUser()->login($login);

            $method = $this->formatAuthorizedMethod($this->getAction());
            $this->tryCall($method, $this->getParameters());
        } catch (AuthenticationException $exception) {
            $this->httpAuthPrompt();
        }
    }

    private function httpAuthPrompt(): void
    {
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
     * @throws \Exception
     */
    private function tryGithub(): void
    {
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
