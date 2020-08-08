<?php

namespace FKSDB\Components\Forms\Controls\DateInputs;

use Nette\Utils\DateTime;
use Nette\Forms\Controls\TextInput;
use Nette\Utils\Html;

/**
 * Class AbstractDateInput
 * @author Michal Červeňák <miso@fykos.cz>
 * @property \DateTimeInterface $value
 */
abstract class AbstractDateInput extends TextInput {

    /**
     * AbstractDateInput constructor.
     * @param null $label
     * @param null $maxLength
     */
    public function __construct($label = NULL, $maxLength = NULL) {
        parent::__construct($label, $maxLength);
        $this->setType($this->getType());
    }

    /**
     * @return Html
     */
    public function getControl() {
        $control = parent::getControl();
        if ($this->value) {
            $control->value = $this->value->format($this->getFormat());
        }
        return $control;
    }

    /**
     * @param string|DateTime $value
     * @return static
     */
    public function setValue($value) {
        if ($value) {
            $this->value = DateTime::from($value);
        } else {
            $this->value = null;
        }
        return $this;
    }

    /**
     * @return string|"datetime-local"|"month"|time"|"date"|"week"
     */
    abstract protected function getType(): string;

    abstract protected function getFormat(): string;
}
