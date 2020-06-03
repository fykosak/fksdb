<?php

namespace FKSDB\Events\Processings;

use FKSDB\Events\Machine\BaseMachine;
use FKSDB\Events\Machine\Machine;
use FKSDB\Events\Model\Holder\Holder;
use FKSDB\Logging\ILogger;
use Nette\Application\UI\Control;
use Nette\ComponentModel\Component;
use Nette\Forms\Form;
use Nette\Forms\IControl;
use Nette\SmartObject;
use Nette\Utils\ArrayHash;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
abstract class AbstractProcessing implements IProcessing {
    use SmartObject;

    public const DELIMITER = '.';
    public const WILDCART = '*';

    /**
     * @var mixed
     */
    private $valuesPathCache;
    /**
     * @var mixed
     */
    private $formPathCache;

    private array $states;

    private Holder $holder;
    /**
     * @var mixed
     */
    private $values;

    final public function process(array $states, ArrayHash $values, Machine $machine, Holder $holder, ILogger $logger, ?Form $form): void {
        $this->states = $states;
        $this->holder = $holder;
        $this->setValues($values);
        $this->setForm($form);
        $this->_process($states, $values, $machine, $holder, $logger, $form);
    }

    abstract protected function _process(array $states, ArrayHash $values, Machine $machine, Holder $holder, ILogger $logger, ?Form $form): void;

    /**
     * @param $mask
     * @return bool
     */
    final protected function hasWildcart($mask) {
        return strpos($mask, self::WILDCART) !== false;
    }

    /**
     *
     * @param string $mask
     * @return IControl[]
     */
    final protected function getValue($mask) {
        $keys = array_keys($this->valuesPathCache);
        $pMask = str_replace(self::WILDCART, '__WC__', $mask);
        $pMask = preg_quote($pMask);
        $pMask = str_replace('__WC__', '(.+)', $pMask);
        $pattern = "/^$pMask\$/";
        $result = [];
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
    final protected function getControl($mask) {
        $keys = array_keys($this->formPathCache);
        $pMask = str_replace(self::WILDCART, '__WC__', $mask);
        $pMask = preg_quote($pMask);
        $pMask = str_replace('__WC__', '(.+)', $pMask);
        $pattern = "/^$pMask\$/";
        $result = [];
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
     * @param $name
     * @return bool
     */
    final protected function isBaseReallyEmpty($name) {
        $baseHolder = $this->holder->getBaseHolder($name);
        if ($baseHolder->getModelState() == BaseMachine::STATE_INIT) {
            return true; // it was empty since begining
        }
        if (isset($this->states[$name]) && $this->states[$name] == BaseMachine::STATE_TERMINATED) {
            return true; // it has been deleted by user
        }
        return false;
    }

    /**
     * @param ArrayHash $values
     * @param string $prefix
     */
    private function setValues(ArrayHash $values, $prefix = ''): void {
        if (!$prefix) {
            $this->values = $values;
            $this->valuesPathCache = [];
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

    /**
     * @param Form $form
     */
    private function setForm($form) {
        $this->formPathCache = [];
        if (!$form) {
            return;
        }
        /** @var Control $control */
        // TODO not type safe
        foreach ($form->getComponents(true, IControl::class) as $control) {
            $path = $control->lookupPath(Form::class);
            $path = str_replace('_1', '', $path);
            $path = str_replace(Component::NAME_SEPARATOR, self::DELIMITER, $path);
            $this->formPathCache[$path] = $control;
        }
    }

}
