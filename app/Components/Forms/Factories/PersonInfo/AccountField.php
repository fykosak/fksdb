<?php

namespace FKSDB\Components\Forms\Factories\PersonInfo;

use FKSDB\Components\Forms\Controls\WriteOnlyInput;
use Nette\Forms\Form;

/**
 * Class AccountField
 * @package FKSDB\Components\Forms\Factories\PersonInfo
 */
class AccountField extends WriteOnlyInput {

    public function __construct() {
        parent::__construct(_('Číslo bankovního účtu'));
        $this->addRule(Form::MAX_LENGTH, null, 32);
    }
}
