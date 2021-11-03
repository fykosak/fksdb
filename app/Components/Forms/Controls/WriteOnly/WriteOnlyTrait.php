<?php

namespace FKSDB\Components\Forms\Controls\WriteOnly;

use FKSDB\Components\Controls\Loaders\JavaScriptCollector;
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
 */
trait WriteOnlyTrait {

    private bool $writeOnly = true;

    private bool $actuallyDisabled = false;

    private bool $hasManualValue = false;

    private bool $writeOnlyAttachedOnValidate = false;

    private bool $writeOnlyAttachedJS = false;

    private function writeOnlyAppendMonitors(): void {
        $this->monitor(Form::class, function (Form $form) {
            if (!$this->writeOnlyAttachedOnValidate) {
                $form->onValidate = $form->onValidate ?: [];
                array_unshift($form->onValidate, function (): void {
                    if ($this->writeOnly && $this->getValue() == self::VALUE_ORIGINAL) {
                        $this->writeOnlyDisable();
                    }
                });
                $this->writeOnlyAttachedOnValidate = true;
            }
        });
        $this->monitor(JavaScriptCollector::class, function (JavaScriptCollector $collector) {
            if (!$this->writeOnlyAttachedJS) {
                $this->writeOnlyAttachedJS = true;
                $collector->registerJSFile('js/writeOnlyInput.js');
            }
        });
    }

    public function getWriteOnly(): bool {
        return $this->writeOnly;
    }

    public function setWriteOnly(bool $writeOnly = true): void {
        $this->writeOnly = $writeOnly;
    }

    private function writeOnlyAdjustControl(Html $control): Html {
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

    protected function writeOnlyLoadHttpData(): void {
        if ($this->getValue() != self::VALUE_ORIGINAL) {
            $this->hasManualValue = true;
        }
    }

    private function writeOnlyDisable(): void {
        $this->actuallyDisabled = $this->isDisabled();
        $this->setDisabled();
    }
}
