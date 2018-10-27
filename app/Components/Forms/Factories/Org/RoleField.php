<?php

namespace FKSDB\Components\Forms\Factories\Org;

use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;

class RoleField extends TextInput {
    public function __construct() {
        parent::__construct(_('Funkce'));
        $this->addRule(Form::MAX_LENGTH, null, 255);
    }
}
