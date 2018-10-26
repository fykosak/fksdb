<?php

namespace FKSDB\Components\Forms\Factories\School;

use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;

class NameField extends TextInput {
    public function __construct() {
        parent::__construct(_('Název'));
        $this->addRule(Form::MAX_LENGTH, null, 255);
        $this->addRule(Form::FILLED, _('Název je povinný.'));
        $this->setOption('description', _('Název na obálku.'));
    }
}
