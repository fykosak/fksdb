<?php

use Nette\Application\UI\Form;
use Nette\ComponentModel\IContainer as IComponentContainer;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class FormLogin extends Form {

    public function __construct(IComponentContainer $parent = NULL, $name = NULL) {
        parent::__construct($parent, $name);

        $this->addText('other_name', 'Jméno')
                ->setDisabled();
        $this->addText('family_name', 'Příjmení')
                ->setDisabled();

        $this->addText('email', 'E-mail')
                ->addRule(Form::FILLED, 'Vyplňte e-mail.')
                ->addRule(Form::EMAIL);
    }

}
