<?php

use Authorization\ContestAuthorizator;
use Nette\Http\UserStorage;

/**
 * Presenter allows authenticated user access only.
 */
abstract class AuthenticatedPresenter extends BasePresenter {

    /**
     * @var ContestAuthorizator
     */
    protected $contestAuthorizator;

    public function getContestAuthorizator() {
        return $this->contestAuthorizator;
    }

    public function injectContestAuthorizator(ContestAuthorizator $contestAuthorizator) {
        $this->contestAuthorizator = $contestAuthorizator;
    }

    protected function startup() {
        parent::startup();
        if (!$this->getUser()->isLoggedIn()) {
            $this->loginRedirect();
        }
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

}
