<?php

namespace FKSDB\Components\Forms\Controls;

use Nette\Forms\Controls\TextBase;
use Nette\Utils\DateTime;
use Nette\Forms\Controls\TextInput;
use Nette\Utils\Html;

/**
 * Class DateInput
 * @package FKSDB\Components\Forms\Controls
 */
class DateInput extends TextInput {

    const FORMAT = 'Y-m-d';

    /**
     * DateInput constructor.
     * @param null $label
     * @param null $cols
     * @param null $maxLength
     */
    public function __construct($label = NULL, $cols = NULL, $maxLength = NULL) {
        parent::__construct($label, $cols, $maxLength);
        $this->setType('date');
    }

    /**
     * @return Html
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
     * @return TextBase|void
     */
    public function setValue($value) {
        if ($value) {
            $this->value = DateTime::from($value);
        } else {
            $this->value = null;
        }
    }
}
