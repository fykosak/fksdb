<?php

namespace FKSDB\Components\Forms\Controls;

use Nette\DateTime;
use Nette\Forms\Controls\TextInput;

/**
 * Class DateInput
 * @package FKSDB\Components\Forms\Controls
 */
class DateInput extends TextInput {

    const FORMAT = 'Y-m-d';

    /**
     * DateInput constructor.
     * @param null $label
     * @param null $maxLength
     */
    public function __construct($label = NULL, $maxLength = NULL) {
        parent::__construct($label, $maxLength);
        $this->setType('date');
    }

    /**
     * @return \Nette\Utils\Html
     */
    public function getControl() {
        $control = parent::getControl();
        if ($this->value) {
            $control->value = $this->value->format(self::FORMAT);
        }

        return $control;
    }

    /**
     * @param $value
     * @return \Nette\Forms\Controls\TextBase|void
     */
    public function setValue($value) {
        if ($value) {
            $this->value = DateTime::from($value);
        } else {
            $this->value = null;
        }
    }
}
