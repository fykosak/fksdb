<?php

namespace FKSDB\Components\Forms\Factories\PersonInfo;

use FKS\Components\Forms\Controls\URLTextBox;

class HomepageField extends URLTextBox implements \IReactField {
    use \ReactFieldDefinition;

    public function __construct() {
        parent::__construct(_('Homepage'));
    }

    public function getReactDefinition(): \ReactField {
        return $this->createReactDefinition();
    }
}
