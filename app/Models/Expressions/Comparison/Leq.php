<?php

declare(strict_types=1);

namespace FKSDB\Models\Expressions\Comparison;

use FKSDB\Models\Expressions\EvaluatedExpression;
use FKSDB\Models\Transitions\Holder\ModelHolder;

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

    public function __invoke(ModelHolder $holder): bool
    {
        return $this->evaluateArgument($this->aValue, $holder) <=
            $this->evaluateArgument($this->bValue, $holder);
    }

    public function __toString(): string
    {
        return "$this->aValue <= $this->bValue";
    }
}
