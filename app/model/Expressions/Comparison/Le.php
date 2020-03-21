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

    /**
     * Le constructor.
     * @param $a
     * @param $b
     */
    function __construct($a, $b) {
        $this->a = $a;
        $this->b = $b;
    }

    /**
     * @return bool
     */
    public function __invoke() {
        $args = func_get_args();
        return $this->evalArg($this->a, $args) < $this->evalArg($this->b, $args);
    }

    /**
     * @return string
     */
    public function __toString() {
        return "{$this->a} < {$this->b}";
    }

}
