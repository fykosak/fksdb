<?php

namespace FKSDB\Components\Forms\Factories\Person;

use Nette\Forms\Controls\TextInput;

class FamilyNameField extends TextInput implements \IReactField {
    use \ReactFieldDefinition;

    public function __construct() {
        parent::__construct(_('Příjmení'));
    }

    public function getReactDefinition(): \ReactField {
        return $this->createReactDefinition();
    }
}
