<?php

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class FormPersonFind extends NAppForm {

    const ID_PERSON = 'person_id';
    const FULLNAME = 'fullname';

    public function __construct(IComponentContainer $parent = NULL, $name = NULL) {
        parent::__construct($parent, $name);

        $this->addText(self::FULLNAME, 'Jméno a příjmení');
        $this->addRadioList(self::ID_PERSON, 'Existující osoba');
    }

}
