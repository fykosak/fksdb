<?php

namespace FKSDB\Components\Forms\Factories\PersonInfo;
class PhoneParentMField extends IPhoneField {
    public function __construct() {
        parent::__construct(_('Telefonní číslo (matka)'));
    }
}
