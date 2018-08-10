<?php

namespace FKSDB\Components\Forms\Factories\Person;

use Nette\Forms\Controls\RadioList;

class GenderField extends RadioList {

    use \ReactFieldDefinition;

    public function __construct() {
        parent::__construct(_('Pohlaví'), $this->createOptions());
        $this->setDefaultValue('M');
    }

    private function createOptions() {
        return ['M' => 'muž', 'F' => 'žena'];
    }

    public function getReactDefinition(): \ReactField {
        $def = $this->createReactDefinition();
        $def->setData($this->createOptions());
        return $def;
    }
}
