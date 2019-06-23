<?php

namespace FKSDB\Components\Forms\Factories\PersonInfo;


use Nette\Forms\Controls\TextInput;

/**
 * Class LinkedinIdField
 * @package FKSDB\Components\Forms\Factories\PersonInfo
 */
class LinkedinIdField extends TextInput {
    public function __construct() {
        parent::__construct(_('Linkedin Id'));
    }
}
