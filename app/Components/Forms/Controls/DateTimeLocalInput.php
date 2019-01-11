<?php


namespace FKSDB\Components\Forms\Controls;


use Nette\DateTime;
use Nette\Forms\Controls\TextInput;

class DateTimeLocalInput extends TextInput {

    const FORMAT = 'Y-m-d\TH:i:s';

    public function __construct($label = NULL, $maxLength = NULL) {
        parent::__construct($label, $maxLength);
        $this->setType('datetime-local');
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
