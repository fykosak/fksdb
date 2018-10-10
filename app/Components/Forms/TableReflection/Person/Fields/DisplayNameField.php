<?php

namespace FKSDB\Components\Forms\TableReflection\Person\Fields;

use Nette\Forms\Controls\TextInput;

class DisplayNameField extends TextInput {

    public function __construct() {
        parent::__construct(_('Zobrazované jméno'));
        $this->setOption('description', _('Pouze pokud je odlišné od "jméno příjmení".'));
    }
}
