<?php

namespace FKSDB\Components\Forms\Factories\PersonInfo;

use FKS\Components\Forms\Controls\WriteonlyInput;
use FKSDB\Components\Forms\Rules\BornNumber;
use Nette\Forms\Form;

class BornIdField extends WriteonlyInput {

    public function __construct() {
        parent::__construct(_('Rodné číslo'));
        $this->setOption('description', _('U cizinců prázdné.'));
        $this->addCondition(Form::FILLED)
            ->addRule(new BornNumber(), _('Rodné číslo nemá platný formát.'));
    }
}
