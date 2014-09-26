<?php

namespace FKS\Components\Forms\Controls;

use Nette\DateTime;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class DateTimeBox extends TextInput {

    const FORMAT = 'Y-m-d H:i:s';

    public function __construct($label = NULL, $cols = NULL, $maxLength = NULL) {
        parent::__construct($label, $cols, $maxLength);

        $this->addCondition(Form::FILLED)
                ->addRule(Form::REGEXP, _('%label očekává YYYY-MM-DD hh:mm[:ss].'), '/^\d{4}-\d{2}-\d{2} [0-2]?\d:[0-5]\d(:[0-5]\d)?$/');
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
