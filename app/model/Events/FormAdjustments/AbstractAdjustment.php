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

    const DELIMITER = '.';
    const WILDCART = '*';
    /** @var array */
    private $pathCache;

    final public function adjust(Form $form, Machine $machine, Holder $holder): void {
        $this->setForm($form);
        $this->_adjust($form, $machine, $holder);
    }

    abstract protected function _adjust(Form $form, Machine $machine, Holder $holder): void;

    /**
     * @param string $mask
     * @return bool
     */
    final protected function hasWildcart($mask): bool {
        return strpos($mask, self::WILDCART) !== false;
    }

    /**
     *
     * @param string $mask
     * @return IControl[]
     */
    final protected function getControl($mask) {
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
