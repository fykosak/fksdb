<?php

namespace FKSDB\Components\Forms\Factories\PersonInfo;

use Nette\Forms\Controls\TextInput;

class AcademicDegreePrefixField extends TextInput {
    public function __construct() {
        parent::__construct(_('Titul před jménem'));
    }
}
