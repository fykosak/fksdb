<?php

namespace FKSDB\Components\Forms\Factories\School;


use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;

class ICField extends TextInput {
    public function __construct() {
        parent::__construct(_('IČ'));
        $this->addRule(Form::MAX_LENGTH, _('Délka IČ je omezena na %d znaků.'), 8);
    }
}
