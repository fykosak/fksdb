<?php

namespace FKSDB\Components\Forms\Controls;

use FKSDB\Components\Forms\Containers\IWriteOnly;
use FKSDB\Components\Forms\Controls\DateInputs\DateInput;
use Nette\ComponentModel\IComponent;
use Nette\Utils\Html;

/**
 * When user doesn't fill it (i.e. desires original value), it behaves like disabled.
 * Only FILLED validation works properly because there's used special value to distinguish unchanged input.
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class WriteOnlyDatePicker extends DateInput implements IWriteOnly {

    use WriteOnlyTrait;

    /**
     * WriteOnlyDatePicker constructor.
     * @param null $label
     */
    public function __construct($label = NULL) {
        parent::__construct($label);
        $this->writeOnlyAppendMonitors();
    }

    /**
     * @return Html
     */
    public function getControl() {
        $control = parent::getControl();
        $control = $this->writeOnlyAdjustControl($control);
        return $control;
    }

    /**
     * @param $value
     * @return static
     */
    public function setValue($value) {
        if ($value == self::VALUE_ORIGINAL) {
            $this->value = $value;
        } else {
            parent::setValue($value);
        }
        return $this;
    }

    public function loadHttpData() {
        parent::loadHttpData();
        $this->writeOnlyLoadHttpData();
    }

    /**
     * @param IComponent $obj
     * @return void
     */
    protected function attached($obj) {
        parent::attached($obj);
        $this->writeOnlyAttached($obj);
    }

}
