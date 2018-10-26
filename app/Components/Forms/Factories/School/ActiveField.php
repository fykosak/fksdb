<?php

namespace FKSDB\Components\Forms\Factories\School;


use Nette\Forms\Controls\Checkbox;

class ActiveField extends Checkbox {
    public function __construct() {
        parent::__construct(_('Aktivní záznam'));
    }
}
