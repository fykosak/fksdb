<?php

namespace FKSDB\Components\Forms\Factories\Person;

use Nette\Forms\Controls\TextInput;

class DisplayNameField extends TextInput implements \IReactField {
    use \ReactFieldDefinition;

    public function __construct() {
        parent::__construct(_('Zobrazované jméno'));
        $this->setOption('description', _('Pouze pokud je odlišené od "jméno příjmení".'));
    }

    public function getReactDefinition(): \ReactField {
        return $this->createReactDefinition();
    }
}
