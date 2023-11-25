<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Processing;

use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Machine\Machine;
use Fykosak\Utils\Logging\Logger;
use Nette\Application\UI\Control;
use Nette\ComponentModel\IComponent;
use Nette\Forms\Control as FormControl;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Form;
use Nette\SmartObject;
use Nette\Utils\ArrayHash;

abstract class AbstractProcessing implements Processing
{
    use SmartObject;

    public const DELIMITER = '.';
    public const WILD_CART = '*';
    private array $valuesPathCache;
    private array $formPathCache;
    /** @var BaseHolder */
    private ModelHolder $holder;

    final public function process(ArrayHash $values): void
    {
        $this->holder = $holder;
        $this->setValues($values);
        $this->setForm($form);
        $this->innerProcess($values, $holder, $logger);
    }

    abstract protected function innerProcess(ArrayHash $values, ModelHolder $holder, Logger $logger): void;

    /**
     * @return FormControl[]
     */
    final protected function getValue(string $mask): array
    {
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
    final protected function getControl(string $mask): array
    {
        $pMask = str_replace('__WC__', '(.+)', preg_quote(str_replace(self::WILD_CART, '__WC__', $mask)));
        $result = [];
        foreach (array_keys($this->formPathCache) as $key) {
            if (preg_match("/^$pMask\$/", $key)) {
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
    protected function isBaseReallyEmpty(): bool
    {
        return $this->holder->getModelState()->value === Machine::STATE_INIT;
    }

    private function setValues(ArrayHash $values, string $prefix = ''): void
    {
        if (!$prefix) {
            $this->valuesPathCache = [];
        }

        foreach ($values as $key => $value) {
            $key = $prefix . str_replace('_container', '', $key);
            if ($value instanceof ArrayHash) {
                $this->setValues($value, $key . self::DELIMITER);
            } else {
                $this->valuesPathCache[$key] = $value;
            }
        }
    }

    private function setForm(Form $form): void
    {
        $this->formPathCache = [];
        /** @var Control $control */
        // TODO not type safe
        foreach ($form->getComponents(true, FormControl::class) as $control) {
            if ($control instanceof BaseControl) {
                $control->loadHttpData();
            }
            $path = $control->lookupPath(Form::class);
            $path = str_replace('_container', '', $path);
            $path = str_replace(IComponent::NAME_SEPARATOR, self::DELIMITER, $path);
            $this->formPathCache[$path] = $control;
        }
    }
}
