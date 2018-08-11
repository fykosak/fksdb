<?php

namespace FKSDB\Components\Forms\Factories\PersonInfo;

class PhoneParentDField extends IPhoneField {
    public function __construct() {
        parent::__construct(_('Telefonní číslo (otec)'));
    }
}
