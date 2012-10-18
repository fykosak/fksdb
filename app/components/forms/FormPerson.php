<?php

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class FormPerson extends NAppForm {

    public function __construct(IComponentContainer $parent = NULL, $name = NULL) {
        parent::__construct($parent, $name);

        $this->addText('first_name', 'Jméno')
                ->addRule(NForm::FILLED, 'Vyplňte jméno.');
        $this->addText('last_name', 'Příjmení')
                ->addRule(NForm::FILLED, 'Vyplňte příjmení.');
        $this->addRadioList('gender', 'Pohlaví', array(
                    'F' => 'žena',
                    'M' => 'muž',
                ))
                ->addRule(NForm::FILLED, 'Vyberte pohlaví.');
        ;
    }

}
