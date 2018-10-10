<?php

namespace FKSDB\Components\Forms\TableReflection\Person\Fields;

use Nette\Forms\Controls\TextInput;

class FamilyNameField extends TextInput {

    public function __construct() {
        parent::__construct(_('Příjmení'));
    }

}
