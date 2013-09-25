<?php

use Nette\Forms\Form;



/**
 * @author Jan Kubalek 
*/
class FiziklaniOnlineForm extends Form {
	public function __construct() {
		$school_select = new SchoolSelect;
		$this->addComponent($school_select);

		$this->addGroup('Člen 1');
		$this->addText('first_name_1', 'Jméno:');
		$this->addText('second_name_1', 'Příjmení:"');
		$this->addText('email_1', 'Email:');

		$this->addGroup('Člen 2');
		$this->addText('first_name_2', 'Jméno:');
		$this->addText('second_name_2', 'Příjmení:"');
		$this->addText('email_2', 'Email:');

		$this->addGroup('Člen 3');
		$this->addText('first_name_3', 'Jméno:');
		$this->addText('second_name_3', 'Příjmení:"');
		$this->addText('email_3', 'Email:');

		$this->addGroup('Člen 4');
		$this->addText('first_name_4', 'Jméno:');
		$this->addText('second_name_4', 'Příjmení:"');
		$this->addText('email_4', 'Email:');

		$this->addProtection();
		$this->addSubmit('Potvrdit');
	}
}
