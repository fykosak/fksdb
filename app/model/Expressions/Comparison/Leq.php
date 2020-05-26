<?php

namespace FKSDB\Expressions\Comparison;

use FKSDB\Expressions\EvaluatedExpression;

/**
 * Less or equal.
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class Leq extends EvaluatedExpression {

    /**
     * @var
     */
    private $aValue;
    /**
     * @var
     */
    private $bValue;

    /**
     * Leq constructor.
     * @param $aValue
     * @param $bValue
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
        return $this->evaluateArgument($this->aValue, ...$args) <= $this->evaluateArgument($this->bValue, ...$args);
    }

    /**
     * @return string
     */
    public function __toString() {
        return "{$this->aValue} <= {$this->bValue}";
    }

}
