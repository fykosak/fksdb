<?php

use Authentication\TokenAuthenticator;
use Authorization\ContestAuthorizator;
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

    const PARAM_AUTH_TOKEN = 'at';

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
        if (!$this->getUser()->isLoggedIn()) {
            $this->tryAuthToken();
            $this->loginRedirect();
        } // TODO else explicitly ignore token?
    }

    protected function loginRedirect() {
        if ($this->user->logoutReason === UserStorage::INACTIVITY) {
            $this->flashMessage('Byl(a) jste příliš dlouho neaktivní a pro jistotu Vás systém odhlásil.');
        } else {
            $this->flashMessage('Musíte se přihlásit k přístupu na požadovanou stránku.');
        }
        $backlink = $this->application->storeRequest();
        $this->redirect(':Authentication:login', array('backlink' => $backlink));
    }

    private function tryAuthToken() {
        $tokenData = $this->getParam(self::PARAM_AUTH_TOKEN);
        if (!$tokenData) {
            return;
        }

        try {
            $login = $this->tokenAuthenticator->authenticate($tokenData);
            $this->getUser()->login($login);
            $this->flashMessage('Úspešné přihlášení pomocí tokenu.');
            $this->redirect('this'); //TODO verify: strip auth token from URL
        } catch (AuthenticationException $e) {
            $this->flashMessage($e->getMessage(), 'error');
        }
    }

}
