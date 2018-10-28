<?php

namespace FKSDB\Components\Forms\Factories\PersonInfo;


use Nette\Forms\Controls\TextInput;

class HealthInsuranceField extends TextInput {
    public function __construct() {
        parent::__construct(_('Zdravotní pojišťovna'));
    }
}
