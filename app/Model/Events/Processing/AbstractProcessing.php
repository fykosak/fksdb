<?php

namespace FKSDB\Model\Events\Processing;

use FKSDB\Model\Events\Machine\Machine;
use FKSDB\Model\Events\Model\Holder\Holder;
use FKSDB\Model\Logging\ILogger;
use Nette\Application\UI\Control;
use Nette\ComponentModel\Component;
use Nette\Forms\Controls\BaseControl;
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
    public const WILD_CART = '*';
    private array $valuesPathCache;
    private array $formPathCache;
    private array $states;
    private Holder $holder;

    /**
     * @param array $states
     * @param ArrayHash $values
     * @param Machine $machine
     * @param Holder $holder
     * @param ILogger $logger
     * @param Form|null $form
     * @return mixed|void
     */
    final public function process(array $states, ArrayHash $values, Machine $machine, Holder $holder, ILogger $logger, ?Form $form = null) {
        $this->states = $states;
        $this->holder = $holder;
        $this->setValues($values);
        $this->setForm($form);
        $this->innerProcess($states, $values, $machine, $holder, $logger, $form);
    }

    abstract protected function innerProcess(array $states, ArrayHash $values, Machine $machine, Holder $holder, ILogger $logger, ?Form $form): void;

    final protected function hasWildCart(string $mask): bool {
        return strpos($mask, self::WILD_CART) !== false;
    }

    /**
     *
     * @param string $mask
     * @return IControl[]
     */
    final protected function getValue(string $mask): array {
        $keys = array_keys($this->valuesPathCache);
        $pMask = str_replace(self::WILD_CART, '__WC__', $mask);
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
    final protected function getControl(string $mask): array {
        $keys = array_keys($this->formPathCache);
        $pMask = str_replace(self::WILD_CART, '__WC__', $mask);
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
     * @param string $name
     * @return bool
     */
    final protected function isBaseReallyEmpty(string $name): bool {
        $baseHolder = $this->holder->getBaseHolder($name);
        if ($baseHolder->getModelState() == \FKSDB\Model\Transitions\Machine\Machine::STATE_INIT) {
            return true; // it was empty since beginning
        }
        if (isset($this->states[$name]) && $this->states[$name] == \FKSDB\Model\Transitions\Machine\Machine::STATE_TERMINATED) {
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

    private function setForm(?Form $form): void {
        $this->formPathCache = [];
        if (!$form) {
            return;
        }
        /** @var Control $control */
        // TODO not type safe
        foreach ($form->getComponents(true, IControl::class) as $control) {
            if ($control instanceof BaseControl) {
                $control->loadHttpData();
            }
            $path = $control->lookupPath(Form::class);
            $path = str_replace('_1', '', $path);
            $path = str_replace(Component::NAME_SEPARATOR, self::DELIMITER, $path);
            $this->formPathCache[$path] = $control;
        }
    }
}