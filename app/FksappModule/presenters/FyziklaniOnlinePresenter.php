<?php

namespace FksappModule;

use \Nette\Application\UI\Form;
use \Nette\Utils\Html;


/**
 * TODO remove(?) backlink
*/
class FyziklaniOnlinePresenter extends AwaPresenter {
	/**
	 * @inheritdoc
	*/

	public function actionDisplay($id) {
	}

	public function actionDela($id) {
	}

	public function actionNewa($id) {
	}

	public function createComponentRegistrationForm() {
		$form = new FyziklaniOnlineForm();

		$form->onSuccess[] = array($this, 'registrationFormOnSuccess');
	}														

	public function registrationFormOnSuccess($form) {
	}
}
