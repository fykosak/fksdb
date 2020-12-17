<?php

namespace FKSDB\Model\Expressions\Comparison;

use FKSDB\Model\Expressions\EvaluatedExpression;

/**
 * Less or equal.
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class Le extends EvaluatedExpression {

    /** @var callable|mixed */
    private $aValue;
    /** @var callable|mixed */
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

    public function __invoke(...$args): bool {
        return $this->evaluateArgument($this->aValue, ...$args) < $this->evaluateArgument($this->bValue, ...$args);
    }

    public function __toString(): string {
        return "{$this->aValue} < {$this->bValue}";
    }

}
