<?php

use Nette\Application\UI\Form;
use Nette\ComponentModel\IContainer as IComponentContainer;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class FormContestant extends Form {

    const SCHOOL = 'school_id';

    public function __construct(IComponentContainer $parent = NULL, $name = NULL) {
        parent::__construct($parent, $name);

        $this->addText('other_name', 'Jméno')
                ->setDisabled();
        $this->addText('family_name', 'Příjmení')
                ->setDisabled();

        $this->addText('study_year', 'Ročník')
                ->addRule(Form::INTEGER, 'Ročník musí být číslo.')
                ->addRule(Form::FILLED, 'Ročník musí být vyplněn.');

        $this->addText('class', 'Třída')
                ->addRule(Form::MAX_LENGTH, 'Příliš dlouhé označní třídy.', 8);

        //TODO better element for school
        //TODO remove dependency on evironment
        $schoolElement = new SchoolElement('Škola', \Nette\Environment::getService('ServiceSchool'));
        $this->addComponent($schoolElement, self::SCHOOL);
    }

    

}
