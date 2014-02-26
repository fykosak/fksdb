<?php

namespace FKS\Expressions\Comparison;

use FKS\Expressions\FunctionExpression;

/**
 * Less or equal.
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class Le extends FunctionExpression {

    private $a;
    private $b;

    function __construct($a, $b) {
        $this->a = $a;
        $this->b = $b;
    }

    public function __invoke() {
        $args = func_get_args();
        return $this->evalArg($this->a, $args) < $this->evalArg($this->b, $args);
    }

    public function __toString() {
        return "{$this->a} < {$this->b}";
    }

}
