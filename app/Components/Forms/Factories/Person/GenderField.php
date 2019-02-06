<?php

namespace FKSDB\Components\Forms\Factories\Person;

use Nette\Forms\Controls\RadioList;

/**
 * Class GenderField
 * @package FKSDB\Components\Forms\Factories\Person
 */
class GenderField extends RadioList {

    public function __construct() {
        parent::__construct(_('Pohlaví'), $this->createOptions());
        $this->setDefaultValue('M');
    }

    /**
     * @return array
     */
    private function createOptions() {
        return ['M' => 'muž', 'F' => 'žena'];
    }
}
