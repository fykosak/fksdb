<?php

namespace FKSDB\Expressions\Comparison;

use FKSDB\Expressions\EvaluatedExpression;

/**
 * Less or equal.
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class Leq extends EvaluatedExpression {

    private $a;
    private $b;

    /**
     * Leq constructor.
     * @param $a
     * @param $b
     */
    function __construct($a, $b) {
        $this->a = $a;
        $this->b = $b;
    }

    /**
     * @param array $args
     * @return bool
     */
    public function __invoke(...$args): bool {
        return $this->evaluateArgument($this->a, ...$args) <= $this->evaluateArgument($this->b, ...$args);
    }

    /**
     * @return string
     */
    public function __toString() {
        return "{$this->a} <= {$this->b}";
    }

}
