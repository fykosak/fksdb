<?php

namespace FKSDB\Components\Forms\Factories\PersonInfo;

use FKSDB\Components\Forms\Controls\WriteOnlyInput;
use Nette\Forms\Form;

class UkLoginField extends WriteOnlyInput {


    public function __construct() {
        parent::__construct(_('Login UK'));
        $this->addRule(Form::MAX_LENGTH, null, 8);
    }
}
