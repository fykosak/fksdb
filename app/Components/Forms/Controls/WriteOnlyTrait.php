<?php

namespace FKSDB\Components\Forms\Controls;

use FKSDB\Application\IJavaScriptCollector;
use Nette\Forms\Form;
use Nette\Utils\Html;

/**
 * When user doesn't fill it (i.e. desires original value), it behaves like disabled.
 * Only FILLED validation works properly because there's used special value to distinguish unchanged input.
 *
 * @note When using this trait, be sure to call:
 *           writeOnlyAppendMonitors in __constuctor
 *           writeOnlyAdjustControl in getControl
 *           writeOnlyLoadHttpData in loadHttpData after original loadHttpData
 *       and writeOnlyAttached in attached.
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
trait WriteOnlyTrait {

    /**
     * @var bool
     */
    private $writeOnly = true;
    /**
     * @var bool
     */
    private $actuallyDisabled = false;
    /**
     * @var bool
     */
    private $hasManualValue = false;
    /**
     * @var bool
     */
    private $writeOnlyAttachedOnValidate = false;
    /**
     * @var bool
     */
    private $writeOnlyAttachedJS = false;

    private function writeOnlyAppendMonitors() {
        $this->monitor(Form::class, function (Form $form) {
            if (!$this->writeOnlyAttachedOnValidate) {
                $form->onValidate = $form->onValidate ?: [];
                array_unshift($form->onValidate, function (Form $form) {
                    if ($this->writeOnly && $this->getValue() == self::VALUE_ORIGINAL) {
                        $this->writeOnlyDisable();
                    }
                });
                $this->writeOnlyAttachedOnValidate = true;
            }
        });
        $this->monitor(IJavaScriptCollector::class, function (IJavaScriptCollector $collector) {
            if (!$this->writeOnlyAttachedJS) {
                $this->writeOnlyAttachedJS = true;
                $collector->registerJSFile('js/writeOnlyInput.js');
            }
        });
    }

    public function getWriteOnly(): bool {
        return $this->writeOnly;
    }

    /**
     * @param bool $writeOnly
     * @return void
     */
    public function setWriteOnly(bool $writeOnly = true) {
        $this->writeOnly = $writeOnly;
    }

    /**
     * @param Html $control
     * @return Html
     */
    private function writeOnlyAdjustControl(Html $control) {
// rendered control may not disabled
        $control->addAttributes([
            'disabled' => $this->actuallyDisabled,
        ]);

// don't show the value (only if it's form displayed after submit)
// for JS
        if ($this->writeOnly && $this->getValue() && !$this->hasManualValue) {
            $control->addAttributes([
                'data-writeOnly' => (int)true,
                'data-writeOnly-value' => self::VALUE_ORIGINAL,
                'data-writeOnly-label' => _('Hidden value'),
                'value' => self::VALUE_ORIGINAL,
            ]);
        }
        return $control;
    }

    protected function writeOnlyLoadHttpData() {
        if ($this->getValue() != self::VALUE_ORIGINAL) {
            $this->hasManualValue = true;
        }
    }

    private function writeOnlyDisable() {
        $this->actuallyDisabled = $this->isDisabled();
        $this->setDisabled();
    }


}
