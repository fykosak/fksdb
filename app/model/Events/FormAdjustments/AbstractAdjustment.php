<?php

namespace FKSDB\Events\FormAdjustments;

use FKSDB\Events\Machine\Machine;
use FKSDB\Events\Model\Holder\Holder;
use Nette\Application\UI\Control;
use Nette\ComponentModel\Component;
use Nette\Forms\Form;
use Nette\Forms\IControl;
use Nette\SmartObject;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
abstract class AbstractAdjustment implements IFormAdjustment {

    use SmartObject;

    public const DELIMITER = '.';
    public const WILD_CART = '*';

    private array $pathCache;

    final public function adjust(Form $form, Machine $machine, Holder $holder): void {
        $this->setForm($form);
        $this->innerAdjust($form, $machine, $holder);
    }

    abstract protected function innerAdjust(Form $form, Machine $machine, Holder $holder): void;

    final protected function hasWildCart(string $mask): bool {
        return strpos($mask, self::WILD_CART) !== false;
    }

    /**
     * @param string $mask
     * @return IControl[]
     */
    final protected function getControl(string $mask): array {
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

    private function setForm(Form $form): void {
        $this->pathCache = [];
        /** @var Control $control */
        // TODO not type safe
        foreach ($form->getComponents(true, IControl::class) as $control) {
            $path = $control->lookupPath(Form::class);
            $path = str_replace('_1', '', $path);
            $path = str_replace(Component::NAME_SEPARATOR, self::DELIMITER, $path);
            $this->pathCache[$path] = $control;
        }
    }
}
