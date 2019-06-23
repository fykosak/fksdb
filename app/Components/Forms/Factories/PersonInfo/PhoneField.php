<?php

namespace FKSDB\Components\Forms\Factories\PersonInfo;

/**
 * Class PhoneField
 * @package FKSDB\Components\Forms\Factories\PersonInfo
 */
class PhoneField extends AbstractPhoneField {
    public function __construct() {
        parent::__construct(_('Telefonní číslo'));
    }

}
