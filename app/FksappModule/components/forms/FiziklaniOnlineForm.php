<?php

use Nette\Forms\Form;



/**
 * @author Jan Kubalek 
*/
class FiziklaniOnlineForm extends Form {
	public function __construct() {
		$school_select = new SchoolSelect;
		$this->addComponent($school_select);

		$this->addGroup(_('Člen 1'));
		$this->addText('first_name_1', _('Jméno:'));
		$this->addText('second_name_1', _('Příjmení:"'));
		$this->addText('email_1', _('Email:'));

		$this->addGroup(_('Člen 2'));
		$this->addText('first_name_2', _('Jméno:'));
		$this->addText('second_name_2', _('Příjmení:"'));
		$this->addText('email_2', _('Email:'));

		$this->addGroup(_('Člen 3'));
		$this->addText('first_name_3', _('Jméno:'));
		$this->addText('second_name_3', _('Příjmení:"'));
		$this->addText('email_3', _('Email:'));

		$this->addGroup(_('Člen 4'));
		$this->addText('first_name_4', _('Jméno:'));
		$this->addText('second_name_4', _('Příjmení:"'));
		$this->addText('email_4', _('Email:'));

		$this->addProtection();
		$this->addSubmit('Potvrdit');
	}
}
