<?php

namespace FKSDB\Components\Forms\Factories\Org;

use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;

class TexSignatureField extends TextInput {
    public function __construct() {
        parent::__construct(_('TeX identifikátor'));
        $this->addRule(Form::MAX_LENGTH, null, 32);
        $this->addCondition(Form::FILLED)
            ->addRule(Form::REGEXP, _('%label obsahuje nepovolené znaky.'), '/^[a-z][a-z0-9._\-]*$/i');
    }
}
