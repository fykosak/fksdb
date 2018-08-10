<?php

namespace FKSDB\Components\Forms\Factories\Person;

use Nette\Forms\Controls\TextInput;

class OtherNameField extends TextInput implements \IReactField {
    use \ReactFieldDefinition;

    public function __construct() {
        parent::__construct(_('Jméno'));
    }

    public function getReactDefinition(): \ReactField {
        return $this->createReactDefinition();
    }
}
