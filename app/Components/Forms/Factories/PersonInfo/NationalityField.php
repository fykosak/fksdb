<?php

namespace FKSDB\Components\Forms\Factories\PersonInfo;


use Nette\Forms\Controls\TextInput;

class NationalityField extends TextInput {
    public function __construct() {
        parent::__construct(_('Státní příslušnost'));
    }
}
