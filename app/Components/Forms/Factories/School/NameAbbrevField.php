<?php

namespace FKSDB\Components\Forms\Factories\School;


use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;

class NameAbbrevField extends TextInput {
    public function __construct() {
        parent::__construct(_('Zkrácený název'));
        $this->addRule(Form::MAX_LENGTH, _('Délka zkráceného názvu je omezena na %d znaků.'), 32);
        $this->addRule(Form::FILLED, _('Zkrácený název je povinný.'));
        $this->setOption('description', _('Název krátký do výsledkovky.'));
    }
}
