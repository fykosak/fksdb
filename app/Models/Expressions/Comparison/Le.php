<?php

declare(strict_types=1);

namespace FKSDB\Models\Expressions\Comparison;

use FKSDB\Models\Expressions\EvaluatedExpression;

class Le extends EvaluatedExpression
{

    /** @var callable|mixed */
    private $aValue;
    /** @var callable|mixed */
    private $bValue;

    /**
     * Le constructor.
     * @param callable|mixed $aValue
     * @param callable|mixed $bValue
     */
    public function __construct($aValue, $bValue)
    {
        $this->aValue = $aValue;
        $this->bValue = $bValue;
    }

    public function __invoke(...$args): bool
    {
        [$holder] = $args;
        return $this->evaluateArgument($this->aValue, $holder) <
            $this->evaluateArgument($this->bValue, $holder);
    }

    public function __toString(): string
    {
        return "$this->aValue < $this->bValue";
    }
}
