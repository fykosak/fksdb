<?php

namespace FKSDB\Components\Forms\Factories\School;

use Nette\Forms\Controls\TextInput;

class FullNameField extends TextInput {
    public function __construct() {
        parent::__construct(_('Plný název'));
        $this->addRule(Form::MAX_LENGTH, null, 255);
        $this->setOption('description', _('Úplný nezkrácený název školy.'));
    }
}
