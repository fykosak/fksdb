<?php

namespace FKSDB\Components\Forms\Factories\PersonInfo;

use FKS\Components\Forms\Controls\WriteonlyInput;
use Nette\Forms\Form;

class AccountField extends WriteonlyInput {

    public function __construct() {
        parent::__construct(_('Číslo bankovního účtu'));
        $this->addRule(Form::MAX_LENGTH, null, 32);
    }
}
