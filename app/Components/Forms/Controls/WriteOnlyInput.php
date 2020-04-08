<?php

namespace FKSDB\Components\Forms\Controls;

use FKSDB\Components\Forms\Containers\IWriteOnly;
use Nette\Forms\Controls\TextInput;

/**
 * When user doesn't fill it (i.e. desires original value), it behaves like disabled.
 * Only FILLED validation works properly because there's used special value to distinguish unchanged input.
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class WriteOnlyInput extends TextInput implements IWriteOnly {

    use WriteOnlyTrait;

    /**
     * WriteOnlyInput constructor.
     * @param null $label
     * @param null $maxLength
     */
    public function __construct($label = NULL,$maxLength = NULL) {
        parent::__construct($label, $maxLength);
        $this->writeOnlyAppendMonitors();
    }

    /**
     * @return \Nette\Utils\Html
     */
    public function getControl() {
        $control = parent::getControl();
        $control = $this->writeOnlyAdjustControl($control);
        return $control;
    }

    public function loadHttpData() {
        parent::loadHttpData();
        $this->writeOnlyLoadHttpData();
    }

    /**
     * @param $obj
     */
    protected function attached($obj) {
        parent::attached($obj);
        $this->writeOnlyAttached($obj);
    }

}
