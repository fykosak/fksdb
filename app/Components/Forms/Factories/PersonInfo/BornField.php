<?php

namespace FKSDB\Components\Forms\Factories\PersonInfo;

use FKSDB\Components\Forms\Controls\WriteOnlyDatePicker;

/**
 * Class BornField
 * @package FKSDB\Components\Forms\Factories\PersonInfo
 */
class BornField extends WriteOnlyDatePicker {

    public function __construct() {
        parent::__construct(_('Datum narození'));
        $this->setDefaultDate((new \DateTime())->modify('-16 years'));
    }
}
