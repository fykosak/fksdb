<?php

namespace FKSDB\Components\Forms\Factories\PersonInfo;

/**
 * Class PhoneParentDField
 * @package FKSDB\Components\Forms\Factories\PersonInfo
 */
class PhoneParentDField extends AbstractPhoneField {
    public function __construct() {
        parent::__construct(_('Telefonní číslo (otec)'));
    }
}
