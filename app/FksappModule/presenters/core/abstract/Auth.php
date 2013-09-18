<?php

namespace FksappModule;
use Nette\Http\UserStorage;



/**
 *
 * Checks if the user is "OK."
 *
 * @package presenters\core\abstract
 *
 * @author Jan Kubalek
*/
abstract class Auth extends BasePresenter {

	/**
	 * Standard Nette startup function
	 * @param  void
	 * @return boolean (true - OK)
	 * @access protected
	*/
	protected function startup() {
		parent::startup();
		if (!$this->getUser()->isLoggedIn()) {
			$this->loginRedirect();
		}

		return true;
	}

	/**
	 * Redirect to login page
	 * @param  void
	 * @return boolean (true - OK)
	 * @access private
	*/
	private function loginRedirect() {
		if ($this->user->logoutReason === UserStorage::INACTIVITY) {
			$this->flashMessage('Byl(a) jste příliš dlouho neaktivní a pro jistotu Vás systém odhlásil.');
		} else {
			$this->flashMessage('Musíte se přihlásit k přístupu na požadovanou stránku.');
		}
		$backlink = $this->application->storeRequest();
		$this->redirect(':Authentication:login', array('backlink' => $backlink));

		return true;
	}
}
