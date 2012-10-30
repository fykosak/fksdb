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

        $this->addText('other_name', 'Jméno')
                ->addRule(Form::FILLED, 'Vyplňte příjmení.')
                ->setOption('description', 'Více jmen oddělte jednou mezerou.');

        $this->addText('family_name', 'Příjmení')
                ->addRule(Form::FILLED, 'Vyplňte příjmení.')
                ->setOption('description', 'Více příjmení oddělte jednou mezerou.');


        $this->addRadioList('gender', 'Pohlaví', array(
                    'F' => 'žena',
                    'M' => 'muž',
                ))
                ->addRule(Form::FILLED, 'Vyberte pohlaví.');
        ;
    }

}
