<?php

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class FormPostContacts extends NAppForm {

    public function __construct(IComponentContainer $parent = NULL, $name = NULL) {
        parent::__construct($parent, $name);

        $this->addText('first_name', 'Jméno')->setDisabled();
        $this->addText('last_name', 'Příjmení')->setDisabled();

        //TODO dynamic container
        $this->addText('street', 'Ulice');
        $this->addText('house_nr', 'Č P/O');
        $this->addText('city', 'Město')
                ->addRule(NForm::FILLED);
        $this->addText('postal_code', 'PSČ')
                ->addRule(NForm::MAX_LENGTH, null, 5);
        $this->addSelect('country_iso', 'Stát', array(
            'CZ' => 'Česká republika',
            'SK' => 'Slovensko',
        ));
        //TODO get region from PSČ
    }

}
