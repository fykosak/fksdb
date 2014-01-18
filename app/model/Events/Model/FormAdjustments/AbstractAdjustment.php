<?php

namespace Events\Model\FormAdjustments;

use Events\Machine\Machine;
use Events\Model\Holder;
use Events\Model\IFormAdjustment;
use Nette\ComponentModel\Component;
use Nette\Forms\Form;
use Nette\Forms\IControl;
use Nette\InvalidArgumentException;
use Nette\Object;
use Nette\Utils\Arrays;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
abstract class AbstractAdjustment extends Object implements IFormAdjustment {

    const DELIMITER = '.';
    const WILDCART = '*';

    private $pathCache;
    private $rules;

    function __construct($rules) {
        $this->rules = $rules;
    }

    public function adjust(Form $form, Machine $machine, Holder $holder) {
        $this->setForm($form);

        foreach ($this->rules as $target => $prerequisities) {
            if (is_scalar($prerequisities)) {
                $prerequisities = array($prerequisities);
            }

            foreach ($prerequisities as $prerequisity) {
                $cTarget = $this->getControl($target);
                $cPrerequisity = $this->getControl($prerequisity);

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
                    throw new InvalidArgumentException("Cannot apply 1:1, 1:n, n:1 neither matching rule to '$target ($sTarget match(es)): $prerequisity ($sPrerequisity match(es))'.");
                }
            }
        }
    }

    abstract protected function processPair(IControl $target, IControl $prerequisity);

    protected final function hasWildcart($mask) {
        return strpos($mask, self::WILDCART) !== false;
    }

    protected final function getControl($mask) {
        $keys = array_keys($this->pathCache);
        $pMask = str_replace(self::WILDCART, '__WC__', $mask);
        $pMask = preg_quote($pMask);
        $pMask = str_replace('__WC__', '(.+)', $pMask);
        $pattern = "/^$pMask\$/";
        $result = array();
        foreach ($keys as $key) {
            $matches = array();
            if (preg_match($pattern, $key, $matches)) {
                if (isset($matches[1])) {
                    $result[$matches[1]] = $this->pathCache[$key];
                } else {
                    $result[] = $this->pathCache[$key];
                }
            }
        }
        return $result;
    }

    private function setForm($form) {
        $this->pathCache = array();
        foreach ($form->getComponents(true, 'Nette\Forms\IControl') as $control) {
            $path = $control->lookupPath('Nette\Forms\Form');
            $path = str_replace('_1', '', $path);
            $path = str_replace(Component::NAME_SEPARATOR, self::DELIMITER, $path);
            $this->pathCache[$path] = $control;
        }
    }

}

