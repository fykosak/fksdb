<?php

namespace FKSDB\Components\Forms\Factories\School;


use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;

class EmailField extends TextInput {
    public function __construct() {
        parent::__construct(_('Kontaktní e-mail'));
        $this->addCondition(Form::FILLED)
            ->addRule(Form::EMAIL);
    }
}
