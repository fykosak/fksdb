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
        [$holder] = $args;
        return $this->evaluateArgument($this->aValue, $holder) <=
            $this->evaluateArgument($this->bValue, $holder);
    }
}
