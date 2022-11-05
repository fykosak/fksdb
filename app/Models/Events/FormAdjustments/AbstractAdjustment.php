<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\FormAdjustments;

use FKSDB\Models\Transitions\Holder\ModelHolder;
use Nette\Application\UI\Control;
use Nette\ComponentModel\IComponent;
use Nette\Forms\Form;
use Nette\Forms\Control as FormControl;
use Nette\SmartObject;

abstract class AbstractAdjustment implements FormAdjustment
{
    use SmartObject;

    public const DELIMITER = '.';
    public const WILD_CART = '*';

    private array $pathCache;

    final public function adjust(Form $form, ModelHolder $holder): void
    {
        $this->setForm($form);
        $this->innerAdjust($form, $holder);
    }

    abstract protected function innerAdjust(Form $form, ModelHolder $holder): void;

    final protected function hasWildCart(string $mask): bool
    {
        return strpos($mask, self::WILD_CART) !== false;
    }

    /**
     * @return FormControl[]
     */
    final protected function getControl(string $mask): array
    {
        $keys = array_keys($this->pathCache);
        $pMask = str_replace(self::WILD_CART, '__WC__', $mask);

        $pMask = str_replace('__WC__', '(.+)', preg_quote($pMask));
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

    private function setForm(Form $form): void
    {
        $this->pathCache = [];
        /** @var Control $control */
        // TODO not type safe
        foreach ($form->getComponents(true, FormControl::class) as $control) {
            $path = $control->lookupPath(Form::class);
            $path = str_replace('_container', '', $path);
            $path = str_replace(IComponent::NAME_SEPARATOR, self::DELIMITER, $path);
            $this->pathCache[$path] = $control;
        }
    }
}
