<?php

namespace FKSDB\Components\Forms\Controls;

use Nette\DateTime;
use Nette\Forms\Controls\TextInput;

class DateInput extends TextInput {

    const FORMAT = 'Y-m-d';

    public function __construct($label = NULL, $cols = NULL, $maxLength = NULL) {
        parent::__construct($label, $cols, $maxLength);
        $this->setType('date');
    }

    public function getControl() {
        $control = parent::getControl();
        if ($this->value) {
            $control->value = $this->value->format(self::FORMAT);
        }

        return $control;
    }

    public function setValue($value) {
        if ($value) {
            $this->value = DateTime::from($value);
        } else {
            $this->value = null;
        }
    }
}
