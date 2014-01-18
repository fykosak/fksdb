<?php

namespace FKS\Components\Forms\Controls;

use FKS\Application\IJavaScriptCollector;
use FKS\Components\Forms\Containers\IWriteonly;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;

/**
 * When user doesn't fill it (i.e. desires original value), it behaves like disabled.
 * Only FILLED validation works properly because there's used special value to distinguish unchanged input.
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class WriteonlyInput extends TextInput implements IWriteonly {

    const VALUE_ORIGINAL = '__original';

    private $writeonly = true;
    private $actuallyDisabled = false;
    private $defaultValue;

    public function __construct($label = NULL, $cols = NULL, $maxLength = NULL) {
        parent::__construct($label, $cols, $maxLength);
        $this->monitor('Nette\Forms\Form');
        $this->monitor('FKS\Application\IJavaScriptCollector');
    }

    public function getWriteonly() {
        return $this->writeonly;
    }

    public function setWriteonly($writeonly = true) {
        $this->writeonly = $writeonly;
    }
    
    public function setDefaultValue($value) {
        parent::setDefaultValue($value);
        $this->defaultValue = $value;
    }

    public function getControl() {
        $control = parent::getControl();

        $form = $this->getForm(FALSE);
        $isSubmitted = $form && $form->isAnchored() && $form->isSubmitted();

        // for JS
        if ($this->writeonly && $this->defaultValue && (!$isSubmitted || ($this->getValue() && $this->getValue() != self::VALUE_ORIGINAL))) {
            $control->data['writeonly'] = (int) true;
            $control->data['writeonly-value'] = self::VALUE_ORIGINAL;
            $control->data['writeonly-label'] = _('skrytá hodnota');
        }

        // rendered control may not disabled
        $control->disabled = $this->actuallyDisabled;

        // don't show the value (only if it's form displayed after submit)

        if ($this->writeonly && !$isSubmitted && $this->getValue() && $this->getValue() != self::VALUE_ORIGINAL) {
            $control->value = self::VALUE_ORIGINAL;
        }
        return $control;
    }

    private function disable() {
        $this->actuallyDisabled = $this->isDisabled();
        $this->setDisabled();
    }

    private $attachedOnValidate = false;
    private $attachedJS = false;

    protected function attached($obj) {
        parent::attached($obj);
        if (!$this->attachedOnValidate && $obj instanceof Form) {
            $that = $this;
            array_unshift($obj->onValidate, function(Form $form) use($that) {
                        if ($that->writeonly && $that->getValue() == self::VALUE_ORIGINAL) {
                            $that->disable();
                        }
                    });
            $this->attachedOnValidate = true;
        }
        if (!$this->attachedJS && $obj instanceof IJavaScriptCollector) {
            $this->attachedJS = true;
            $obj->registerJSFile('js/writeonlyInput.js');
        }
    }

}
