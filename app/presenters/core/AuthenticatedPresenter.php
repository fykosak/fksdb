<?php

use Authorization\ContestAuthorizator;
use Nette\Http\UserStorage;

/**
 * Presenter allows authenticated user access only.
 * 
 * User can be authenticated in the session (after successful login)
 * or via an authentication token. It's responsibility of the particular
 * operation to dispose the token after use (if it should be so).
 */
abstract class AuthenticatedPresenter extends BasePresenter {

    const PARAM_AUTH_TOKEN = 'at';
    const SESSION_NS = 'auth';

    /**
     * @var ServiceAuthToken
     */
    private $authTokenService;

    /**
     * @var ContestAuthorizator
     */
    protected $contestAuthorizator;

    public function injectContestAuthorizator(ContestAuthorizator $contestAuthorizator) {
        $this->contestAuthorizator = $contestAuthorizator;
    }

    public function injectAuthTokenService(ServiceAuthToken $authTokenService) {
        $this->authTokenService = $authTokenService;
    }

    public function getContestAuthorizator() {
        return $this->contestAuthorizator;
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
        $token = $this->authTokenService->verifyToken($tokenData);
        if (!$token) {
            $this->flashMessage('Autentizační token je neplatný.', 'error');
            return;
        }
        // login by the identity
        $login = $token->getLogin();
        if (!$login->active) {
            $this->flashMessage('Neaktivní účet.', 'error'); //TODO keep occurence of this message at one place only
            return;
        }
        $this->getUser()->login($login);

        $this->storeAuthToken($token);
        $this->flashMessage('Úspešné přihlášení pomocí tokenu.');
        $this->redirect('this'); //TODO verify: strip auth token from URL
    }

    protected function disposeAuthToken() {
        $section = $this->getSession(self::SESSION_NS);
        if (isset($section->token)) {
            $this->authTokenService->disposeToken($section->token);
            unset($section->token);
        }
    }

    /**
     * @return bool true iff user has been authenticated by the authentication token
     */
    protected function isAuthenticatedByToken() {
        $section = $this->getSession(self::SESSION_NS);
        return isset($section->token);
    }

    private function storeAuthToken(ModelAuthToken $token) {
        $section = $this->getSession(self::SESSION_NS);
        $section->token = $token->token;
    }

}
