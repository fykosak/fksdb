<?php

use Authentication\TokenAuthenticator;
use Authorization\ContestAuthorizator;
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

    protected function startup() {
        parent::startup();

        // successfull token authentication overwrites the user identity (if any)
        $this->tryAuthToken();

        // if token did nod succeed redirect to login credentials page
        if (!$this->getUser()->isLoggedIn()) {
            $this->loginRedirect();
        }
    }

    protected function loginRedirect() {
        if ($this->user->logoutReason === UserStorage::INACTIVITY) {
            $this->flashMessage('Byl(a) jste příliš dlouho neaktivní a pro jistotu Vás systém odhlásil.', self::FLASH_INFO);
        } else {
            $this->flashMessage('Musíte se přihlásit k přístupu na požadovanou stránku.', self::FLASH_ERROR);
        }
        $backlink = $this->application->storeRequest();
        $this->redirect(':Authentication:login', array('backlink' => $backlink));
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
