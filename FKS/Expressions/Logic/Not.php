<?php

namespace FKS\Expressions\Logic;

use FKS\Expressions\FunctionExpression;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class Not extends FunctionExpression {

    private $expression;

    function __construct($expression) {
        $this->expression = $expression;
    }

    public function __invoke() {
        $args = func_get_args();
        return !$this->evalArg($this->expression, $args);
    }

    public function __toString() {
        return "!({$this->expression})";
    }

}
