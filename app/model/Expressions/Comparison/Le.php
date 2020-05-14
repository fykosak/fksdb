<?php

namespace FKSDB\Expressions\Comparison;

use FKSDB\Expressions\EvaluatedExpression;

/**
 * Less or equal.
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class Le extends EvaluatedExpression {

    private $aValue;
    private $bValue;

    /**
     * Le constructor.
     * @param callable|mixed $aValue
     * @param callable|mixed $bValue
     */
    public function __construct($aValue, $bValue) {
        $this->aValue = $aValue;
        $this->bValue = $bValue;
    }

    /**
     * @param array $args
     * @return bool
     */
    public function __invoke(...$args): bool {
        return $this->evaluateArgument($this->aValue, ...$args) < $this->evaluateArgument($this->bValue, ...$args);
    }

    /**
     * @return string
     */
    public function __toString() {
        return "{$this->aValue} < {$this->bValue}";
    }

}
