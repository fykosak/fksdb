<?php

namespace FKSDB\Models\Events\Processing;

use FKSDB\Models\Events\Machine\Machine;
use FKSDB\Models\Events\Model\Holder\Holder;
use Fykosak\Utils\Logging\Logger;
use Nette\Application\UI\Control;
use Nette\ComponentModel\Component;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Form;
use Nette\Forms\Control as FormControl;
use Nette\SmartObject;
use Nette\Utils\ArrayHash;

abstract class AbstractProcessing implements Processing {

    use SmartObject;

    public const DELIMITER = '.';
    public const WILD_CART = '*';
    private array $valuesPathCache;
    private array $formPathCache;
    private array $states;
    private Holder $holder;

    final public function process(array $states, ArrayHash $values, Machine $machine, Holder $holder, Logger $logger, ?Form $form = null): ?array {
        $this->states = $states;
        $this->holder = $holder;
        $this->setValues($values);
        $this->setForm($form);
        $this->innerProcess($states, $values, $holder, $logger, $form);
        return null;
    }

    abstract protected function innerProcess(array $states, ArrayHash $values, Holder $holder, Logger $logger, ?Form $form): void;

    final protected function hasWildCart(string $mask): bool {
        return strpos($mask, self::WILD_CART) !== false;
    }

    /**
     * @return FormControl[]
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
     * @return FormControl[]
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
     */
    protected function isBaseReallyEmpty(string $name): bool {
        $baseHolder = $this->holder->getBaseHolder($name);
        if ($baseHolder->getModelState() == \FKSDB\Models\Transitions\Machine\Machine::STATE_INIT) {
            return true; // it was empty since beginning
        }
        if (isset($this->states[$name]) && $this->states[$name] == \FKSDB\Models\Transitions\Machine\Machine::STATE_TERMINATED) {
            return true; // it has been deleted by user
        }
        return false;
    }

    private function setValues(ArrayHash $values, string $prefix = ''): void {
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
        foreach ($form->getComponents(true, FormControl::class) as $control) {
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
