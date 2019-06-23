<?php

namespace FKSDB\Components\Forms\Factories\PersonInfo;


use Nette\Forms\Controls\TextInput;

/**
 * Class EmployerField
 * @package FKSDB\Components\Forms\Factories\PersonInfo
 */
class EmployerField extends TextInput {
    public function __construct() {
        parent::__construct(_('Zaměstnavatel'));
    }
}
