<?php

namespace FKSDB\Expressions\Comparison;

use FKSDB\Expressions\EvaluatedExpression;

/**
 * Less or equal.
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class Le extends EvaluatedExpression {

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
