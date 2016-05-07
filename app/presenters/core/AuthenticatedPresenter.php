<?php

use Authentication\GithubAuthenticator;
use Authentication\PasswordAuthenticator;
use Authentication\TokenAuthenticator;
use Authorization\ContestAuthorizator;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Diagnostics\Debugger;
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
     * @var ContestAuthorizator
     */
    protected $contestAuthorizator;

    public function injectTokenAuthenticator(TokenAuthenticator $tokenAuthenticator) {
        $this->tokenAuthenticator = $tokenAuthenticator;
    }

    public function injectPasswordAuthenticator(PasswordAuthenticator $passwordAuthenticator) {
        $this->passwordAuthenticator = $passwordAuthenticator;
    }

    public function injectGithubAuthenticator(GithubAuthenticator $githubAuthenticator) {
        $this->githubAuthenticator = $githubAuthenticator;
    }

    public function injectContestAuthorizator(ContestAuthorizator $contestAuthorizator) {
        $this->contestAuthorizator = $contestAuthorizator;
    }

    public function getContestAuthorizator() {
        return $this->contestAuthorizator;
    }

    public function getTokenAuthenticator() {
        return $this->tokenAuthenticator;
    }

    /**
     * Formats action method name.
     * @param  string
     * @return string
     */
    protected static function formatAuthorizedMethod($action) {
        return 'authorized' . $action;
    }

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
        } else if (!$this->isAuthorized()) {
            $this->unauthorizedAccess();
        }
    }

    private function optionalLoginRedirect() {
        if (!$this->requiresLogin()) {
            return;
        }
        $this->loginRedirect();
    }

    protected final function loginRedirect() {
        if ($this->user->logoutReason === UserStorage::INACTIVITY) {
            $reason = AuthenticationPresenter::REASON_TIMEOUT;
        } else {
            $reason = AuthenticationPresenter::REASON_AUTH;
        }
        $backlink = $this->application->storeRequest(); //TODO this doesn't work in cross domain environment
        $this->redirect(':Authentication:login', array('backlink' => $backlink, AuthenticationPresenter::PARAM_REASON => $reason));
    }

    /**
     * This method may be overriden, however only simple conditions
     * can be checked there -- user session is not prepared at the
     * moment of the call.
     * 
     * @return boolean
     */
    public function requiresLogin() {
        return true;
    }

    /**
     * It may be overriden (should return realm).
     * @return boolean|string
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

    protected function unauthorizedAccess() {
        throw new ForbiddenRequestException();
    }

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
        } catch (AuthenticationException $e) {
            $this->flashMessage($e->getMessage(), self::FLASH_ERROR);
        }
    }

    private function tryHttpAuth() {
        if (!isset($_SERVER['PHP_AUTH_USER'])) {
            $this->httpAuthPrompt();
            return;
        }
        try {
            $credentials = array(
                PasswordAuthenticator::USERNAME => $_SERVER['PHP_AUTH_USER'],
                PasswordAuthenticator::PASSWORD => $_SERVER['PHP_AUTH_PW'],
            );
            $login = $this->passwordAuthenticator->authenticate($credentials);

            Debugger::log("$login signed in using HTTP authentication.");

            $this->getUser()->login($login);

            $method = $this->formatAuthorizedMethod($this->getAction());
            $this->tryCall($method, $this->getParameter());
        } catch (AuthenticationException $e) {
            $this->httpAuthPrompt();
        }
    }

    private function httpAuthPrompt() {
        $realm = $this->getHttpRealm();
        if ($realm && $this->requiresLogin()) {
            header('WWW-Authenticate: Basic realm="' . $realm . '"');
            header('HTTP/1.0 401 Unauthorized');
            echo '<h1>Unauthorized</h1>';
            exit;
        }
    }

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
        } catch (AuthenticationException $e) {
            throw new BadRequestException(_('Chyba autentizace.'), 403, $e);
        }
    }

}
