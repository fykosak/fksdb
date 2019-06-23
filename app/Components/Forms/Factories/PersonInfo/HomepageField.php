<?php

namespace FKSDB\Components\Forms\Factories\PersonInfo;

use FKSDB\Components\Forms\Controls\URLTextBox;

/**
 * Class HomepageField
 * @package FKSDB\Components\Forms\Factories\PersonInfo
 */
class HomepageField extends URLTextBox {

    public function __construct() {
        parent::__construct(_('Homepage'));
    }
}
