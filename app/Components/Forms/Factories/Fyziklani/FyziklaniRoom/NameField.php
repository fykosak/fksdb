<?php

namespace FKSDB\Components\Forms\Factories\Fyziklani\FyziklaniRoom;

use Nette\Forms\Controls\TextInput;

class NameField extends TextInput {
    public function __construct() {
        parent::__construct(_('Name of room'));
    }
}
