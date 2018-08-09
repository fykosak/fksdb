<?php

namespace FKSDB\Components\Forms\Factories\PersonInfo;

use FKS\Components\Forms\Controls\WriteonlyDatePicker;

class BornField extends WriteonlyDatePicker implements \IReactField {
    use \ReactFieldDefinition;

    public function __construct() {
        parent::__construct(_('Datum narození'));
        $this->setDefaultDate((new \DateTime())->modify('-16 years'));
    }

    public function getReactDefinition(): \ReactField {
        return $this->createReactDefinition();
    }
}
