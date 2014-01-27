<?php

namespace FKS\Expressions;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
abstract class VariadicExpression extends FunctionExpression {

    private $arguments;

    public function __construct() {
        $this->arguments = func_get_args();
    }

    public function __invoke() {
        $args = func_get_args();
        return $this->evaluate($args);
    }

    abstract protected function evaluate($args);

    protected function evalArgAt($index, $args) {
        return $this->evalArg($this->arguments[$index], $args);
    }

    protected function getArg($index) {
        return $this->arguments[$index];
    }

    public function getArity() {
        return count($this->arguments);
    }

}
