<?php

namespace FKSDB\Components\Forms\Factories\Org;

use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;

class DomainAliasField extends TextInput {
    public function __construct() {
        parent::__construct(_('Jméno v doméně fykos.cz/vyfuk.mff.cuni.cz'));
        $this->addRule(Form::MAX_LENGTH, null, 32);
        $this->addCondition(Form::FILLED);
        $this->addRule(Form::REGEXP, _('%l obsahuje nepovolené znaky.'), '/^[a-z][a-z0-9._\-]*$/i');
    }
}
