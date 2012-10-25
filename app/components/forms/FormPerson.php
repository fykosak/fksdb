<?php

use Nette\Application\UI\Form;
use Nette\ComponentModel\IContainer as IComponentContainer;
/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class FormPerson extends Form {

    public function __construct(IComponentContainer $parent = NULL, $name = NULL) {
        parent::__construct($parent, $name);

        $this->addText('display_name', 'Zobrazované jméno')
                ->addRule(Form::FILLED, 'Vyplňte jméno.');
        $this->addRadioList('gender', 'Pohlaví', array(
                    'F' => 'žena',
                    'M' => 'muž',
                ))
                ->addRule(Form::FILLED, 'Vyberte pohlaví.');
        ;
    }

}
