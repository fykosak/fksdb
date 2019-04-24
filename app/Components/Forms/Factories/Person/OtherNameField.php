<?php

namespace FKSDB\Components\Forms\Factories\Person;

use Nette\Forms\Controls\TextInput;

/**
 * Class OtherNameField
 * @package FKSDB\Components\Forms\Factories\Person
 */
class OtherNameField extends TextInput {

    public function __construct() {
        parent::__construct(_('Jméno'));
    }
}
