<?php

namespace FKSDB\Components\Forms\TableReflection\Person\Fields;

use Nette\Forms\Controls\RadioList;

class GenderField extends RadioList {

    public function __construct() {
        parent::__construct(_('Pohlaví'), $this->createOptions());
        $this->setDefaultValue('M');
    }

    private function createOptions() {
        return ['M' => 'muž', 'F' => 'žena'];
    }
}
