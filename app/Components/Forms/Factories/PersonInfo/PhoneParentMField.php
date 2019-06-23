<?php

namespace FKSDB\Components\Forms\Factories\PersonInfo;

/**
 * Class PhoneParentMField
 * @package FKSDB\Components\Forms\Factories\PersonInfo
 */
class PhoneParentMField extends AbstractPhoneField {
    public function __construct() {
        parent::__construct(_('Telefonní číslo (matka)'));
    }
}
