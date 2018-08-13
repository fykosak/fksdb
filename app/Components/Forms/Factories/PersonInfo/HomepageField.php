<?php

namespace FKSDB\Components\Forms\Factories\PersonInfo;

use FKS\Components\Forms\Controls\URLTextBox;

class HomepageField extends URLTextBox {

    public function __construct() {
        parent::__construct(_('Homepage'));
    }
}
