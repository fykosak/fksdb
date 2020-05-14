<?php

use Authentication\GithubAuthenticator;
use Authentication\PasswordAuthenticator;
use Authentication\TokenAuthenticator;
use Authorization\ContestAuthorizator;
use Authorization\EventAuthorizator;
use FKSDB\ORM\Models\ModelAuthToken;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
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

    const AUTH_ALLOW_LOGIN = 0x1;
    const AUTH_ALLOW_HTTP = 0x2;
    const AUTH_ALLOW_TOKEN = 0x4;
    const AUTH_ALLOW_GITHUB = 0x8;

    /**
     * @var TokenAuthenticator
     */
    private $tokenAuthenticator;

    /**
     * @var PasswordAuthenticator
     */
    private $passwordAuthenticator;

    /**
     * @var GithubAuthenticator
     */
    private $githubAuthenticator;
    /**
     * @var EventAuthorizator
     */
    private $eventAuthorizator;

    /**
     * @var ContestAuthorizator
     */
    protected $contestAuthorizator;

    /**
     * @param TokenAuthenticator $tokenAuthenticator
     */
    public function injectTokenAuthenticator(TokenAuthenticator $tokenAuthenticator) {
        $this->tokenAuthenticator = $tokenAuthenticator;
    }

    /**
     * @param PasswordAuthenticator $passwordAuthenticator
     */
    public function injectPasswordAuthenticator(PasswordAuthenticator $passwordAuthenticator) {
        $this->passwordAuthenticator = $passwordAuthenticator;
    }

    /**
     * @param GithubAuthenticator $githubAuthenticator
     */
    public function injectGithubAuthenticator(GithubAuthenticator $githubAuthenticator) {
        $this->githubAuthenticator = $githubAuthenticator;
    }

    /**
     * @param ContestAuthorizator $contestAuthorizator
     */
    public function injectContestAuthorizator(ContestAuthorizator $contestAuthorizator) {
        $this->contestAuthorizator = $contestAuthorizator;
    }

    /**
     * @return ContestAuthorizator
     */
    public function getContestAuthorizator(): ContestAuthorizator {
        return $this->contestAuthorizator;
    }

    /**
     * @param EventAuthorizator $eventAuthorizator
     */
    public function injectEventAuthorizator(EventAuthorizator $eventAuthorizator) {
        $this->eventAuthorizator = $eventAuthorizator;
    }

    /**
     * @return EventAuthorizator
     */
    public function getEventAuthorizator(): EventAuthorizator {
        return $this->eventAuthorizator;
    }

    /**
     * @return TokenAuthenticator
     */
    public function getTokenAuthenticator() {
        return $this->tokenAuthenticator;
    }

    /**
     * Formats action method name.
     * @param string
     * @return string
     */
    protected static function formatAuthorizedMethod($action) {
        return 'authorized' . $action;
    }

    /**
     * @param $element
     * @throws ForbiddenRequestException
     */
    public function checkRequirements($element) {
        parent::checkRequirements($element);
        if ($element instanceof ReflectionClass) {
            $this->setAuthorized($this->isAuthorized() && $this->getUser()->isLoggedIn());
            if ($this->isAuthorized()) { // check authorization
                $method = $this->formatAuthorizedMethod($this->getAction());
                $this->tryCall($method, $this->getParameter());
            }
        }
    }

    /**
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     * @throws AbortException
     * @throws Exception
     */
    protected function startup() {
        parent::startup();

        $methods = $this->getAllowedAuthMethods();

        if ($methods & self::AUTH_ALLOW_TOKEN) {
            // successfull token authentication overwrites the user identity (if any)
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
    private function optionalLoginRedirect() {
        if (!$this->requiresLogin()) {
            return;
        }
        $this->loginRedirect();
    }

    /**
     * @throws AbortException
     */
    protected final function loginRedirect() {
        if ($this->user->logoutReason === UserStorage::INACTIVITY) {
            $reason = AuthenticationPresenter::REASON_TIMEOUT;
        } else {
            $reason = AuthenticationPresenter::REASON_AUTH;
        }

        $this->redirect(':Authentication:login', [
            'backlink' => $this->storeRequest(),
            AuthenticationPresenter::PARAM_REASON => $reason
        ]);
    }

    /**
     * This method may be overriden, however only simple conditions
     * can be checked there -- user session is not prepared at the
     * moment of the call.
     *
     * @return bool
     */
    public function requiresLogin() {
        return true;
    }

    /**
     * It may be overriden (should return realm).
     * @return bool|string
     */
    public function getAllowedAuthMethods() {
        return self::AUTH_ALLOW_LOGIN | self::AUTH_ALLOW_TOKEN;
    }

    /**
     * @return string
     */
    protected function getHttpRealm() {
        return null;
    }

    /**
     * @throws ForbiddenRequestException
     */
    protected function unauthorizedAccess() {
        throw new ForbiddenRequestException;
    }

    /**
     * @throws AbortException
     */
    private function tryAuthToken() {
        $tokenData = $this->getParam(TokenAuthenticator::PARAM_AUTH_TOKEN);

        if (!$tokenData) {
            return;
        }

        try {
            $login = $this->tokenAuthenticator->authenticate($tokenData);
            Debugger::log("$login signed in using token $tokenData.");
            if ($this->tokenAuthenticator->isAuthenticatedByToken(ModelAuthToken::TYPE_SSO)) {
                $this->tokenAuthenticator->disposeAuthToken();
            } else {
                $this->flashMessage(_('Úspešné přihlášení pomocí tokenu.'), self::FLASH_INFO);
            }

            $this->getUser()->login($login);
            $this->redirect('this');
        } catch (AuthenticationException $exception) {
            $this->flashMessage($exception->getMessage(), self::FLASH_ERROR);
        }
    }

    /**
     *
     */
    private function tryHttpAuth() {
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
            $this->tryCall($method, $this->getParameter());
        } catch (AuthenticationException $exception) {
            $this->httpAuthPrompt();
        }
    }

    /**
     *
     */
    private function httpAuthPrompt() {
        $realm = $this->getHttpRealm();
        if ($realm && $this->requiresLogin()) {
            header('WWW-Authenticate: Basic realm="' . $realm . '"');
            header('HTTP/1.0 401 Unauthorized');
            echo '<h1>Unauthorized</h1>';
            exit;
        }
    }

    /**
     * @throws BadRequestException
     */
    private function tryGithub() {
        if (!$this->getHttpRequest()->getHeader('X-GitHub-Event')) {
            return;
        }

        try {
            $login = $this->githubAuthenticator->authenticate($this->getFullHttpRequest());

            Debugger::log("$login signed in using Github authentication.");

            $this->getUser()->login($login);

            $method = $this->formatAuthorizedMethod($this->getAction());
            $this->tryCall($method, $this->getParameter());
        } catch (AuthenticationException $exception) {
            throw new ForbiddenRequestException(_('Chyba autentizace.'), \Nette\Http\Response::S403_FORBIDDEN, $exception);
        }
    }

}
