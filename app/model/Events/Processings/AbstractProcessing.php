<?php

namespace Events\Processings;

use Events\Machine\BaseMachine;
use Events\Machine\Machine;
use Events\Model\Holder\Holder;
use FKSDB\Logging\ILogger;
use Nette\ArrayHash;
use Nette\ComponentModel\Component;
use Nette\Forms\Form;
use Nette\Forms\IControl;
use Nette\Object;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
abstract class AbstractProcessing extends Object implements IProcessing {

    const DELIMITER = '.';
    const WILDCART = '*';

    private $valuesPathCache;
    private $formPathCache;
    private $states;
    private $holder;
    private $values;

    public final function process($states, ArrayHash $values, Machine $machine, Holder $holder, ILogger $logger, Form $form = null) {
        $this->states = $states;
        $this->holder = $holder;
        $this->setValues($values);
        $this->setForm($form);
        $this->_process($states, $values, $machine, $holder, $logger, $form);
    }

    abstract protected function _process($states, ArrayHash $values, Machine $machine, Holder $holder, ILogger $logger, Form $form = null);

    protected final function hasWildcart($mask) {
        return strpos($mask, self::WILDCART) !== false;
    }

    /**
     *
     * @param string $mask
     * @return IControl[]
     */
    protected final function getValue($mask) {
        $keys = array_keys($this->valuesPathCache);
        $pMask = str_replace(self::WILDCART, '__WC__', $mask);
        $pMask = preg_quote($pMask);
        $pMask = str_replace('__WC__', '(.+)', $pMask);
        $pattern = "/^$pMask\$/";
        $result = array();
        foreach ($keys as $key) {
            if (preg_match($pattern, $key)) {
                $result[] = $this->valuesPathCache[$key];
            }
        }
        return $result;
    }

    /**
     *
     * @param string $mask
     * @return IControl[]
     */
    protected final function getControl($mask) {
        $keys = array_keys($this->formPathCache);
        $pMask = str_replace(self::WILDCART, '__WC__', $mask);
        $pMask = preg_quote($pMask);
        $pMask = str_replace('__WC__', '(.+)', $pMask);
        $pattern = "/^$pMask\$/";
        $result = array();
        foreach ($keys as $key) {
            if (preg_match($pattern, $key)) {
                $result[] = $this->formPathCache[$key];
            }
        }
        return $result;
    }

    /**
     * Checks whether base is really empty after a value
     * from it wasn't loaded.
     * When it returns false, correct value can be loaded from the model
     * (which is not updated yet).
     *
     * @return boolean
     */
    protected final function isBaseReallyEmpty($name) {
        $baseHolder = $this->holder[$name];
        if ($baseHolder->getModelState() == BaseMachine::STATE_INIT) {
            return true; // it was empty since begining
        }
        if (isset($this->states[$name]) && $this->states[$name] == BaseMachine::STATE_TERMINATED) {
            return true; // it has been deleted by user
        }
        return false;
    }

    private function setValues(ArrayHash $values, $prefix = '') {
        if (!$prefix) {
            $this->values = $values;
            $this->valuesPathCache = array();
        }

        foreach ($values as $key => $value) {
            $key = $prefix . str_replace('_1', '', $key);
            if ($value instanceof ArrayHash) {
                $this->setValues($value, $key . self::DELIMITER);
            } else {
                $this->valuesPathCache[$key] = $value;
            }
        }
    }

    private function setForm($form) {
        $this->formPathCache = array();
        if (!$form) {
            return;
        }
        foreach ($form->getComponents(true, 'Nette\Forms\IControl') as $control) {
            $path = $control->lookupPath('Nette\Forms\Form');
            $path = str_replace('_1', '', $path);
            $path = str_replace(Component::NAME_SEPARATOR, self::DELIMITER, $path);
            $this->formPathCache[$path] = $control;
        }
    }

}

