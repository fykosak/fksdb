<?php

namespace FKSDB\Components\Forms\Factories\PersonInfo;

use FKSDB\Components\Forms\Controls\WriteOnlyInput;
use Nette\Forms\Form;

class IdNumberField extends WriteOnlyInput {

    public function __construct() {
        parent::__construct(_('Číslo OP'));
        $this->setOption('description', _('U cizinců číslo pasu.'));
        $this->addRule(Form::MAX_LENGTH, null, 32);
    }
}
