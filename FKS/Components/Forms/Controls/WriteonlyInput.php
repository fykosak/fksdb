<?php

namespace FKS\Components\Forms\Controls;

use FKS\Components\Forms\Containers\IWriteonly;
use Nette\Forms\Controls\TextInput;

/**
 * When user doesn't fill it (i.e. desires original value), it behaves like disabled.
 * Only FILLED validation works properly because there's used special value to distinguish unchanged input.
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class WriteonlyInput extends TextInput implements IWriteonly {

    use WriteonlyTrait;

    public function __construct($label = NULL, $cols = NULL, $maxLength = NULL) {
        parent::__construct($label, $cols, $maxLength);
        $this->writeonlyAppendMonitors();
    }

    public function getControl() {
        $control = parent::getControl();
        $control = $this->writeonlyAdjustControl($control);
        return $control;
    }

    public function loadHttpData() {
        parent::loadHttpData();
        $this->writeonlyLoadHttpData();
    }

    protected function attached($obj) {
        parent::attached($obj);
        $this->writeonlyAttached($obj);
    }

}
