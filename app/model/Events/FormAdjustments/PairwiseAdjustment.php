<?php

namespace Events\FormAdjustments;

use Events\Machine\Machine;
use Events\Model\Holder\Holder;
use Nette\Forms\Form;
use Nette\Forms\IControl;
use Nette\InvalidArgumentException;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
abstract class PairwiseAdjustment extends AbstractAdjustment implements IFormAdjustment {

    const DELIMITER = '.';
    const WILDCART = '*';

    private $rules;

    function __construct($rules) {
        $this->rules = $rules;
    }

    protected function _adjust(Form $form, Machine $machine, Holder $holder) {
        foreach ($this->rules as $target => $prerequisities) {
            if (is_scalar($prerequisities)) {
                $prerequisities = array($prerequisities);
            }

            foreach ($prerequisities as $prerequisity) {
                $cTarget = $this->getControl($target);
                $cPrerequisity = $this->getControl($prerequisity);

                if (!$cTarget || !$cPrerequisity) {
                    break;
                }
                if ($this->hasWildcart($target) && $this->hasWildcart($prerequisity)) {
                    foreach ($cTarget as $key => $control) {
                        if (isset($cPrerequisity[$key])) {
                            $this->processPair($control, $cPrerequisity[$key]);
                        }
                    }
                } else if (count($cTarget) == 1) {
                    foreach ($cPrerequisity as $control) {
                        $this->processPair(reset($cTarget), $control);
                    }
                } else if (count($cPrerequisity) == 1) {
                    foreach ($cTarget as $control) {
                        $this->processPair($control, reset($cPrerequisity));
                    }
                } else {
                    $sTarget = count($cTarget);
                    $sPrerequisity = count($cPrerequisity);
                    throw new InvalidArgumentException("Cannot apply 1:1, 1:n, n:1 neither matching rule to '$target ($sTarget match(es)): $prerequisity ($sPrerequisity match(es))'.");
                }
            }
        }
    }

    abstract protected function processPair(IControl $target, IControl $prerequisity);
}

