<?php

namespace FKSDB\Components\Forms\Factories\Person;

use Nette\Forms\Controls\TextInput;

/**
 * Class DisplayNameField
 * @package FKSDB\Components\Forms\Factories\Person
 */
class DisplayNameField extends TextInput {

    public function __construct() {
        parent::__construct(_('Zobrazované jméno'));
        $this->setOption('description', _('Pouze pokud je odlišné od "jméno příjmení".'));
    }
}
