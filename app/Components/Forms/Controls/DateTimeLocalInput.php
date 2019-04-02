<?php


namespace FKSDB\Components\Forms\Controls;


use Nette\Utils\DateTime;
use Nette\Forms\Controls\TextInput;

/**
 * Class DateTimeLocalInput
 * @package FKSDB\Components\Forms\Controls
 */
class DateTimeLocalInput extends TextInput {

    const FORMAT = 'Y-m-d\TH:i:s';

    /**
     * DateTimeLocalInput constructor.
     * @param null $label
     * @param null $cols
     * @param null $maxLength
     */
    public function __construct($label = NULL, $cols = NULL, $maxLength = NULL) {
        parent::__construct($label, $cols, $maxLength);
        $this->setType('datetime-local');
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
