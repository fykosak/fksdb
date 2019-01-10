<?php

namespace Events\FormAdjustments;

use Events\Machine\Machine;
use Events\Model\Holder\Holder;
use Nette\ComponentModel\Component;
use Nette\Forms\Form;
use Nette\Forms\IControl;
use Nette\Object;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
abstract class AbstractAdjustment extends Object implements IFormAdjustment {

    const DELIMITER = '.';
    const WILDCART = '*';

    private $pathCache;

    public final function adjust(Form $form, Machine $machine, Holder $holder) {
        $this->setForm($form);
        $this->_adjust($form, $machine, $holder);
    }

    protected abstract function _adjust(Form $form, Machine $machine, Holder $holder);

    protected final function hasWildcart($mask) {
        return strpos($mask, self::WILDCART) !== false;
    }

    /**
     *
     * @param string $mask
     * @return IControl[]
     */
    protected final function getControl($mask) {
        $keys = array_keys($this->pathCache);
        $pMask = str_replace(self::WILDCART, '__WC__', $mask);
        $pMask = preg_quote($pMask);
        $pMask = str_replace('__WC__', '(.+)', $pMask);
        $pattern = "/^$pMask\$/";
        $result = [];
        foreach ($keys as $key) {
            $matches = [];
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

    /**
     * @param Form $form
     */
    private function setForm($form) {
        $this->pathCache = [];
        foreach ($form->getComponents(true, 'Nette\Forms\IControl') as $control) {
            $path = $control->lookupPath('Nette\Forms\Form');
            $path = str_replace('_1', '', $path);
            $path = str_replace(Component::NAME_SEPARATOR, self::DELIMITER, $path);
            $this->pathCache[$path] = $control;
        }
    }

}

