<?php

declare(strict_types=1);

namespace FKSDB\Modules\Core;

use FKSDB\Models\Authentication\PasswordAuthenticator;
use FKSDB\Models\Authentication\TokenAuthenticator;
use FKSDB\Models\Authorization\ContestAuthorizator;
use FKSDB\Models\Authorization\EventAuthorizator;
use FKSDB\Modules\CoreModule\AuthenticationPresenter;
use Fykosak\Utils\Logging\Message;
use Nette\Application\ForbiddenRequestException;
use Nette\InvalidStateException;
use Nette\Security\AuthenticationException;
use Tracy\Debugger;

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

    protected TokenAuthenticator $tokenAuthenticator;
    protected PasswordAuthenticator $passwordAuthenticator;
    protected EventAuthorizator $eventAuthorizator;
    protected ContestAuthorizator $contestAuthorizator;

    final public function injectAuthenticated(
        TokenAuthenticator $tokenAuthenticator,
        PasswordAuthenticator $passwordAuthenticator,
        ContestAuthorizator $contestAuthorizator,
        EventAuthorizator $eventAuthorizator
    ): void {
        $this->tokenAuthenticator = $tokenAuthenticator;
        $this->passwordAuthenticator = $passwordAuthenticator;
        $this->contestAuthorizator = $contestAuthorizator;
        $this->eventAuthorizator = $eventAuthorizator;
    }

    /**
     * @param \ReflectionMethod|\ReflectionClass $element
     * @throws \ReflectionException
     */
    public function checkRequirements($element): void
    {
        parent::checkRequirements($element);
        if ($element instanceof \ReflectionClass) {
            $method = $this->formatAuthorizedMethod();
            $this->authorized = $method->invoke($this);
        }
    }

    public function formatAuthorizedMethod(): \ReflectionMethod
    {
        $method = 'authorized' . $this->getAction();
        try {
            $reflectionMethod = new \ReflectionMethod($this, $method);
            if ($reflectionMethod->getReturnType()->getName() !== 'bool') {
                throw new InvalidStateException(
                    sprintf('Method %s of %s should return bool.', $reflectionMethod->getName(), get_class($this))
                );
            }
            if ($reflectionMethod->isAbstract() || !$reflectionMethod->isPublic()) {
                throw new InvalidStateException(
                    sprintf(
                        'Method %s of %s should be public and not abstract.',
                        $reflectionMethod->getName(),
                        get_class($this)
                    )
                );
            }
        } catch (\ReflectionException $exception) {
            throw new InvalidStateException(
                sprintf('Presenter %s has not implemented method %s.', get_class($this), $method)
            );
        }
        return $reflectionMethod;
    }

    /**
     * @throws ForbiddenRequestException
     * @throws \Exception
     */
    protected function startup(): void
    {
        parent::startup();
        $methods = $this->getAllowedAuthMethods();
        if ($methods[self::AUTH_TOKEN]) {
            $this->tryAuthToken();
        }
        if ($methods[self::AUTH_HTTP]) {
            $this->tryHttpAuth();
        }
        if (!$this->getUser()->isLoggedIn() && $methods[self::AUTH_LOGIN]) {
            $this->optionalLoginRedirect();
        }
        if (!$this->authorized) {
            throw new ForbiddenRequestException();
        }
    }

    public function getAllowedAuthMethods(): array
    {
        return [
            self::AUTH_HTTP => false,
            self::AUTH_LOGIN => true,
            self::AUTH_TOKEN => true,
        ];
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
            Debugger::log(sprintf('%s signed in using token %s.', $login->login, $tokenData), 'token-login');
            $this->flashMessage(_('Successful token authentication.'), Message::LVL_INFO);

            $this->getUser()->login($login);
            $this->redirect('this');
        } catch (AuthenticationException $exception) {
            $this->flashMessage($exception->getMessage(), Message::LVL_ERROR);
        }
    }

    /**
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

            Debugger::log(sprintf('%s signed in using HTTP authentication.', $login), 'http-login');
            $this->getUser()->login($login);
            $method = $this->formatAuthorizedMethod();
            $this->authorized = $method->invoke($this);
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

    protected function getHttpRealm(): ?string
    {
        return null;
    }

    /**
     * This method may be override, however only simple conditions
     * can be checked there -- user session is not prepared at the
     * moment of the call.
     */
    public function requiresLogin(): bool
    {
        return true;
    }

    private function optionalLoginRedirect(): void
    {
        if (!$this->requiresLogin()) {
            return;
        }
        $this->redirect(
            ':Core:Authentication:login',
            [
                'backlink' => $this->storeRequest(),
                AuthenticationPresenter::PARAM_REASON => $this->getUser()->logoutReason,
            ]
        );
    }
}
