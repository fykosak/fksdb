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

        $this->addText('display_name', 'Jméno')->setDisabled();

        $this->addText('study_year', 'Ročník')
                ->addRule(Form::INTEGER, 'Ročník musí být číslo.')
                ->addRule(Form::FILLED, 'Ročník musí být vyplněn.');

        $this->addText('class', 'Třída')
                ->addRule(Form::MAX_LENGTH, 'Příliš dlouhé označní třídy.', 8);

        //TODO better element for school
        $this->addSelect(self::SCHOOL, 'Škola');
    }

    public function loadSchools() {
        $service = $this->getPresenter()->getService('ServiceSchool');
        $this[FormContestant::SCHOOL]->setItems($service->getTable()->order('name')->fetchPairs('school_id', 'name'));
    }

}
