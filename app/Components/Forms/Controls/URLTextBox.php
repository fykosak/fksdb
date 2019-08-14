<?php

namespace FKSDB\Components\Forms\Controls;

use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class URLTextBox extends TextInput {

    /**
     * URLTextBox constructor.
     * @param null $label
     * @param null $cols
     * @param null $maxLength
     */
    public function __construct($label = NULL, $cols = NULL, $maxLength = NULL) {
        parent::__construct($label, $cols, $maxLength);

        $this->addCondition(Form::FILLED)
                ->addRule(Form::URL, _('%label není platná URL.'));
    }

    /**
     * @param $value
     * @return \Nette\Forms\Controls\TextBase|void
     */
    public function setValue($value) {
        if ($value) {
            if (!preg_match('#^[a-z]+://#i', $value)) {
                $value = 'http://' . $value;
            }
        }
        parent::setValue($value);
    }

}
