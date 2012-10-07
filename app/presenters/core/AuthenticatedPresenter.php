<?php

/**
 */
abstract class AuthenticatedPresenter extends BasePresenter {

    protected function startup() {
        parent::startup();
        if (!$this->getUser()->isLoggedIn()) {
            $this->loginRedirect();
        }
    }

    protected function loginRedirect() {
        if ($this->user->logoutReason === NUserStorage::INACTIVITY) {
            $this->flashMessage('Byl(a) jste příliš dlouho neaktivní a pro jistotu Vás systém odhlásil.');
        } else {
            $this->flashMessage('Musíte se přihlásit k přístupu na požadovanou stránku.');
        }
        $backlink = $this->application->storeRequest();
        $this->redirect('Authentication:login', array('backlink' => $backlink));
    }

}
