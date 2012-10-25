<?php

use Nette\Application\UI\Form;
use Nette\ComponentModel\IContainer as IComponentContainer;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class FormPersonInfo extends Form {

    public function __construct(IComponentContainer $parent = NULL, $name = NULL) {
        parent::__construct($parent, $name);

        $this->addText('display_name', 'Jméno')->setDisabled();

        $this->addText('born', 'Datum narození'); //TODO date element
        $this->addText('id_number', 'Číslo OP');
        $this->addText('born_id', 'Rodné číslo'); //TODO check
        $this->addText('phone', 'Telefonní číslo');
        $this->addText('im', 'IM');
        $this->addText('note', 'Poznámka');
        $this->addText('uk_login', 'Login UK');
        $this->addText('account', 'Číslo bankovního účtu');
        $this->addText('agreed', 'Poslední souhlas se zpracováním osobních údajů')
                ->setDisabled(); //TODO date element, access control (check box?)
    }

}
