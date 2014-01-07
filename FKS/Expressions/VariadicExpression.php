<?php

namespace FKS\Expressions;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
abstract class VariadicExpression {

    private $arguments;

    public function __construct() {
        $this->arguments = func_get_args();
    }

    public function __invoke() {
        $args = func_get_args();
        return $this->evaluate($args);
    }

    abstract protected function evaluate($args);

    protected function evalArg($index, $args) {
        $evaluated = $this->arguments[$index];
        if (is_callable($evaluated)) {
            return call_user_func_array($evaluated, $args);
        } else {
            return $evaluated;
        }
    }

    protected function getArg($index) {
        return $this->arguments[$index];
    }

    public function getArity() {
        return count($this->arguments);
    }

}
