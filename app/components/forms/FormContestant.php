<?php

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class FormContestant extends NAppForm {

    const SCHOOL = 'school_id';

    public function __construct(IComponentContainer $parent = NULL, $name = NULL) {
        parent::__construct($parent, $name);

        $this->addText('first_name', 'Jméno')->setDisabled();
        $this->addText('last_name', 'Příjmení')->setDisabled();

        $this->addText('study_year', 'Ročník')
                ->addRule(NForm::INTEGER, 'Ročník musí být číslo.')
                ->addRule(NForm::FILLED, 'Ročník musí být vyplněn.');

        $this->addText('class', 'Třída')
                ->addRule(NForm::MAX_LENGTH, 'Příliš dlouhé označní třídy.', 8);

        //TODO better element for school
        $this->addSelect(self::SCHOOL, 'Škola');
    }

    public function loadSchools() {
        $service = $this->getPresenter()->getService('ServiceSchool');
        $this[FormContestant::SCHOOL]->setItems($service->getTable()->fetchPairs('school_id', 'name'));
    }

}
