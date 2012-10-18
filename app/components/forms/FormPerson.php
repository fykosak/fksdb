<?php

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class FormPerson extends NAppForm {

    public function __construct(IComponentContainer $parent = NULL, $name = NULL) {
        parent::__construct($parent, $name);

        $this->addText('display_name', 'Zobrazované jméno')
                ->addRule(NForm::FILLED, 'Vyplňte jméno.');
        $this->addRadioList('gender', 'Pohlaví', array(
                    'F' => 'žena',
                    'M' => 'muž',
                ))
                ->addRule(NForm::FILLED, 'Vyberte pohlaví.');
        ;
    }

}
