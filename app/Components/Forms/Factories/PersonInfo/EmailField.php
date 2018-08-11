<?php

namespace FKSDB\Components\Forms\Factories\PersonInfo;

use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;

class EmailField extends TextInput {

    public function __construct() {
        parent::__construct(_('E-mail'));
        $this->addCondition(Form::FILLED)
            ->addRule(Form::EMAIL, _('Neplatný tvar e-mailu.'));
    }
}
