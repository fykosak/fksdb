<?php

use Authentication\TokenAuthenticator;
use Authorization\ContestAuthorizator;
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
 */
abstract class AuthenticatedPresenter extends BasePresenter {

    /**
     * @var TokenAuthenticator
     */
    private $tokenAuthenticator;

    /**
     * @var ContestAuthorizator
     */
    protected $contestAuthorizator;

    public function injectContestAuthorizator(ContestAuthorizator $contestAuthorizator) {
        $this->contestAuthorizator = $contestAuthorizator;
    }

    public function injectTokenAuthenticator(TokenAuthenticator $tokenAuthenticator) {
        $this->tokenAuthenticator = $tokenAuthenticator;
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

        // successfull token authentication overwrites the user identity (if any)
        $this->tryAuthToken();

        // if token did nod succeed redirect to login credentials page
        if (!$this->getUser()->isLoggedIn()) {
            $this->loginRedirect();
        } else if (!$this->isAuthorized()) {
            $this->unauthorizedAccess();
        }
    }

    private function loginRedirect() {
        if ($this->user->logoutReason === UserStorage::INACTIVITY) {
            $reason = AuthenticationPresenter::REASON_TIMEOUT;
        } else {
            $reason = AuthenticationPresenter::REASON_AUTH;
        }
        $backlink = $this->application->storeRequest(); //TODO this doesn't work in cross domain environment
        $this->redirect(':Authentication:login', array('backlink' => $backlink, AuthenticationPresenter::PARAM_REASON => $reason));
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
                $this->flashMessage('Úspešné přihlášení pomocí tokenu.', self::FLASH_INFO);
            }

            $this->getUser()->login($login);
            $this->redirect('this');
        } catch (AuthenticationException $e) {
            $this->flashMessage($e->getMessage(), self::FLASH_ERROR);
        }
    }

}
