<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\FormAdjustments;

use FKSDB\Models\Transitions\Holder\ModelHolder;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Form;
use Nette\Forms\Control;
use Nette\InvalidArgumentException;

abstract class PairwiseAdjustment extends AbstractAdjustment
{

    protected iterable $rules;

    public function __construct(iterable $rules)
    {
        $this->rules = $rules;
    }

    protected function innerAdjust(Form $form, ModelHolder $holder): void
    {
        foreach ($this->rules as $target => $prerequisites) {
            if (is_scalar($prerequisites)) {
                $prerequisites = [$prerequisites];
            }

            foreach ($prerequisites as $prerequisite) {
                $cTarget = $this->getControl($target);
                $cPrerequisite = $this->getControl($prerequisite);

                if (!$cTarget || !$cPrerequisite) {
                    break;
                }
                if ($this->hasWildCart($target) && $this->hasWildCart($prerequisite)) {
                    foreach ($cTarget as $key => $control) {
                        if (isset($cPrerequisite[$key])) {
                            $this->processPair($control, $cPrerequisite[$key]);
                        }
                    }
                } elseif (count($cTarget) == 1) {
                    foreach ($cPrerequisite as $control) {
                        $this->processPair(reset($cTarget), $control);
                    }
                } elseif (count($cPrerequisite) == 1) {
                    foreach ($cTarget as $control) {
                        $this->processPair($control, reset($cPrerequisite));
                    }
                } else {
                    $sTarget = count($cTarget);
                    $sPrerequisite = count($cPrerequisite);
                    throw new InvalidArgumentException(
                        "Cannot apply 1:1, 1:n, n:1 neither 
                    matching rule to '$target ($sTarget match(es)): $prerequisite ($sPrerequisite match(es))'."
                    );
                }
            }
        }
    }

    abstract protected function processPair(BaseControl $target, Control $prerequisite): void;
}
