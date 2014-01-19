<?php

namespace Events\Processings;

use Events\Machine\Machine;
use Events\Model\Holder\Holder;
use Nette\ArrayHash;
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

    private $pathCache;

    public function process(ArrayHash $values, Machine $machine, Holder $holder) {
        $this->setValues($values);
        $this->_process($values, $machine, $holder);
    }

    abstract protected function _process(ArrayHash $values, Machine $machine, Holder $holder);

    protected final function hasWildcart($mask) {
        return strpos($mask, self::WILDCART) !== false;
    }

    /**
     * 
     * @param string $mask
     * @return IControl[]
     */
    protected final function getValue($mask) {
        $keys = array_keys($this->pathCache);
        $pMask = str_replace(self::WILDCART, '__WC__', $mask);
        $pMask = preg_quote($pMask);
        $pMask = str_replace('__WC__', '(.+)', $pMask);
        $pattern = "/^$pMask\$/";
        $result = array();
        foreach ($keys as $key) {
            if (preg_match($pattern, $key)) {
                $result[] = $this->pathCache[$key];
            }
        }
        return $result;
    }

    private function setValues(ArrayHash $values, $prefix = '') {
        if (!$prefix) {
            $this->pathCache = array();
        }

        foreach ($values as $key => $value) {
            $key = str_replace('_1', '', $key);
            if ($value instanceof ArrayHash) {
                $this->setValues($value, $key . self::DELIMITER);
            } else {
                $this->pathCache[$prefix . $key] = $value;
            }
        }
    }

}

