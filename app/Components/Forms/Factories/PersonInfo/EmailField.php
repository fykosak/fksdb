<?php

namespace FKSDB\Components\Forms\Factories\PersonInfo;

use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;

class EmailField extends TextInput implements \IReactField {
    use \ReactFieldDefinition;

    public function __construct() {
        parent::__construct(_('E-mail'));
        $this->addCondition(Form::FILLED);
        $this->addRule(Form::EMAIL, _('Neplatný tvar e-mailu.'));
    }

    public function getReactDefinition(): \ReactField {
        return $this->createReactDefinition();
    }
}
