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
    }

}
