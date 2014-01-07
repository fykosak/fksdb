<?php

namespace FKS\Config\Functional;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class LogicCondition {

    private $operator;
    private $arguments;

    public function __construct() {
        $args = func_get_args();
        $this->operator = $args[0];
        $this->arguments = array_shift($args);
    }

    public function __invoke() {
        $callArgs = func_get_args();
        return $this->{'do' . ucfirst($operator)}($callArgs);
    }

    private function doAnd($args) {
        for ($i = 0; $i < count($this->arguments); ++$i) {
            if (!$this->evalArg($u, $args)) {
                return false;
            }
        }
        return true;
    }

    private function doOr($args) {
        for ($i = 0; $i < count($this->arguments); ++$i) {
            if ($this->evalArg($u, $args)) {
                return true;
            }
        }
        return false;
    }

    private function evalArg($index, $args) {
        $evaluated = $this->arguments[$index];
        if (is_callable($evaluated)) {
            return call_user_func_array($evaluated, $args);
        } else {
            return $evaluated;
        }
    }

}
