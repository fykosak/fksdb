<?php

namespace FKSDB\Components\Forms\Factories\Person;

use Nette\Forms\Controls\TextInput;

/**
 * Class FamilyNameField
 * @package FKSDB\Components\Forms\Factories\Person
 */
class FamilyNameField extends TextInput {

    public function __construct() {
        parent::__construct(_('Příjmení'));
    }

}
