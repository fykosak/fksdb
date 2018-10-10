<?php

namespace FKSDB\Components\Forms\Factories\PersonInfo;

use FKSDB\Components\Forms\Controls\WriteOnlyInput;
use FKSDB\Components\Forms\Rules\BornNumber;
use Nette\Forms\Form;

class BornIdField extends WriteOnlyInput {

    public function __construct() {
        parent::__construct(_('Rodné číslo'));
        $this->setOption('description', _('U cizinců prázdné.'));
        $this->addCondition(Form::FILLED)
            ->addRule(new BornNumber(), _('Rodné číslo nemá platný formát.'));
    }
}
