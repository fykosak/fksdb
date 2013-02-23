<?php

use \Nette\Forms\Form;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class FormContainerOrg extends FormContainerModel {

    public function __construct(ServicePerson $servicePerson = true) {
        parent::__construct(null, null);

        $this->addText('class', 'Třída');

        $persons = $servicePerson->getTable()->order('family_name, other_name');
        $items = array();
        while ($person = $persons->fetch()) {
            $items[$person->person_id] = $person->getFullname();
        }

        //TODO komponenta výběru osoby
        $this->addSelect('person_id', 'Osoba')
                ->setItems($items);

        //TODO range
        $this->addText('since', 'Od ročníku')
                ->addRule(Form::NUMERIC)
                ->addRule(Form::FILLED);

        $this->addText('until', 'Do ročníku')
                ->addRule(Form::NUMERIC);


        $this->addText('role', 'Funkce')
                ->addRule(Form::MAX_LENGTH, null, 32);

        $this->addTextArea('note', 'Poznámka');

        $this->addText('order', 'Hodnost')
                ->setOption('description', 'Pro řazení v seznamu organizátorů')
                ->addRule(Form::NUMERIC)
                ->addRule(Form::FILLED);

        $this->addText('tex_signature', 'Podpis v TeXu')
                ->addRule(Form::FILLED);
    }

}
