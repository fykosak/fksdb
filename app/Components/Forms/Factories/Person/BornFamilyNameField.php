<?php

namespace FKSDB\Components\Forms\Factories\Person;

use Nette\Forms\Controls\TextInput;

class BornFamilyNameField extends TextInput {
    public function __construct() {
        parent::__construct(_('Rodné příjmení'));
        $this->setOption('description', _('Pouze pokud je odlišné od příjmení.'));
    }
}
