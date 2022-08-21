<?php

declare(strict_types=1);

namespace FKSDB\Models\Expressions\Comparison;

use FKSDB\Models\Expressions\EvaluatedExpression;

class Leq extends EvaluatedExpression
{

    /** @var mixed */
    private $aValue;
    /** @var mixed */
    private $bValue;

    /**
     * Leq constructor.
     * @param mixed $aValue
     * @param mixed $bValue
     */
    public function __construct($aValue, $bValue)
    {
        $this->aValue = $aValue;
        $this->bValue = $bValue;
    }

    public function __invoke(...$args): bool
    {
        return $this->evaluateArgument($this->aValue, ...$args) <= $this->evaluateArgument($this->bValue, ...$args);
    }

    public function __toString(): string
    {
        return "$this->aValue <= $this->bValue";
    }
}
