<?php

namespace FKSDB\Components\Forms\Controls\DateInputs;

use Nette\DateTime;
use Nette\Forms\Controls\TextInput;

abstract class AbstractDateInput extends TextInput {

    public function __construct($label = NULL, $cols = NULL, $maxLength = NULL) {
        parent::__construct($label, $cols, $maxLength);
        $this->setType($this->getType());
    }

    public function getControl() {
        $control = parent::getControl();
        if ($this->value) {
            $control->value = $this->value->format($this->getFormat());
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

    /**
     * @return string|"datetime-local"|"month"|time"|"date"|"week"
     */
    abstract protected function getType(): string;

    abstract protected function getFormat(): string;
}
