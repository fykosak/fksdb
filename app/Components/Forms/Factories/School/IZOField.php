<?php

namespace FKSDB\Components\Forms\Factories\School;

use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;

class IZOField extends TextInput {
    public function __construct() {
        parent::__construct(_('IZO'));
        $this->addRule(Form::MAX_LENGTH, _('Délka IZO je omezena na %d znaků.'), 32);
    }
}
