<?php

namespace FKSDB\Components\Forms\Factories\PersonInfo;


use Nette\Forms\Controls\TextInput;

class EmployerField extends TextInput {
    public function __construct() {
        parent::__construct(_('Zaměstnavatel'));
    }
}
