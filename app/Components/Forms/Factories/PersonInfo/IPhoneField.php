<?php

namespace FKSDB\Components\Forms\Factories\PersonInfo;

use FKS\Components\Forms\Controls\WriteonlyInput;
use Nette\Forms\Form;

abstract class IPhoneField extends WriteonlyInput {

    public function __construct($label) {
        parent::__construct($label);
        $this->setAttribute("placeholder", 've tvaru +420123456789');
        $this->addRule(Form::MAX_LENGTH, null, 32);
        $this->addCondition(Form::FILLED);
        $this->addRule(Form::REGEXP, _('%label smí obsahovat jen číslice a musí být v mezinárodím tvaru začínajícím +421 nebo +420.'), '/\+42[01]\d{9}/');
    }
}
