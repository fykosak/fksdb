<?php

namespace FKS\Components\Forms\Controls;

use FKS\Application\IJavaScriptCollector;
use Nette\Forms\Form;
use Nette\Utils\Html;

/**
 * When user doesn't fill it (i.e. desires original value), it behaves like disabled.
 * Only FILLED validation works properly because there's used special value to distinguish unchanged input.
 * 
 * @note When using this trait, be sure to call:
 *           writeonlyAppendMonitors in __constuctor
 *           writeonlyAdjustControl in getControl
 *       and writeonlyAttached in attached
 * @author Michal Koutný <michal@fykos.cz>
 */
trait WriteonlyTrait {

    private $writeonly = true;
    private $actuallyDisabled = false;
    private $defaultValue;

    private function writeonlyAppendMonitors() {
        $this->monitor('Nette\Forms\Form');
        $this->monitor('FKS\Application\IJavaScriptCollector');
    }

    public function getWriteonly() {
        return $this->writeonly;
    }

    public function setWriteonly($writeonly = true) {
        $this->writeonly = $writeonly;
    }

    public function setValue($value) {
        parent::setValue($value);
        $form = $this->getForm(FALSE);
        $isSubmitted = $form && $form->isAnchored() && $form->isSubmitted();
        if (!$isSubmitted) {
            $this->defaultValue = $value;
        }
        if ($value == self::VALUE_ORIGINAL) {
            $this->value = $value;
        }
    }

    private function writeonlyAdjustControl(Html $control) {
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

    private function writeonlyDisable() {
        $this->actuallyDisabled = $this->isDisabled();
        $this->setDisabled();
    }

    private $writeonlyAttachedOnValidate = false;
    private $writeonlyAttachedJS = false;

    protected function writeonlyAttached($obj) {
        parent::attached($obj);
        if (!$this->writeonlyAttachedOnValidate && $obj instanceof Form) {
            $that = $this;
            $obj->onValidate = $obj->onValidate ? : array();
            array_unshift($obj->onValidate, function(Form $form) use($that) {
                        if ($that->writeonly && $that->getValue() == self::VALUE_ORIGINAL) {
                            $that->writeonlyDisable();
                        }
                    });
            $this->writeonlyAttachedOnValidate = true;
        }
        if (!$this->writeonlyAttachedJS && $obj instanceof IJavaScriptCollector) {
            $this->writeonlyAttachedJS = true;
            $obj->registerJSFile('js/writeonlyInput.js');
        }
    }

}
