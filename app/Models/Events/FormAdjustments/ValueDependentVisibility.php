<?php

declare(strict_types=1);

declare(strict_types=1);

namespace FKSDB\Models\Events\FormAdjustments;

use FKSDB\Models\Events\Model\Holder\Holder;
use Nette\Forms\Control;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Form;
use Nette\InvalidArgumentException;

class ValueDependentVisibility extends PairwiseAdjustment
{
    protected function innerAdjust(Form $form, Holder $holder): void
    {
        foreach ($this->rules as $target => $prerequisite) {
            $cTarget = $this->getControl($target);
            $cPrerequisite = $this->getControl($prerequisite['from']);

            if (!$cTarget || !$cPrerequisite) {
                break;
            }
            if ($this->hasWildCart($target) && $this->hasWildCart($prerequisite['from'])) {
                foreach ($cTarget as $key => $control) {
                    if (isset($cPrerequisite[$key])) {
                        $this->processPair($control, $cPrerequisite[$key], $prerequisite['value']);
                    }
                }
            } elseif (count($cTarget) == 1) {
                foreach ($cPrerequisite as $control) {
                    $this->processPair(reset($cTarget), $control, $prerequisite['value']);
                }
            } elseif (count($cPrerequisite) == 1) {
                foreach ($cTarget as $control) {
                    $this->processPair($control, reset($cPrerequisite), $prerequisite['value']);
                }
            } else {
                $sTarget = count($cTarget);
                $sPrerequisite = count($cPrerequisite);
                throw new InvalidArgumentException(
                    "Cannot apply 1:1, 1:n, n:1 neither matching rule to '$target ($sTarget match(es)): $prerequisite ($sPrerequisite match(es))'."
                );
            }
        }
    }

    /**
     * @param null $value
     */
    protected function processPair(BaseControl $target, Control $prerequisite, $value = null): void
    {
        $target->getRules()->addConditionOn($prerequisite, Form::NOT_EQUAL, $value)->toggle(
            $target->getHtmlId() . '-pair'
        );
    }
}
