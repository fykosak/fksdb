<?php

declare(strict_types=1);

namespace FKSDB\Models\Expressions\Comparison;

use FKSDB\Models\Expressions\EvaluatedExpression;

/**
 * @phpstan-template ArgType
 * @phpstan-extends EvaluatedExpression<bool,scalar,ArgType>
 */
class Leq extends EvaluatedExpression
{
    /** @phpstan-var (callable(ArgType):scalar)|scalar */
    private $aValue;
    /** @phpstan-var (callable(ArgType):scalar)|scalar */
    private $bValue;

    /**
     * @phpstan-param (callable(ArgType):scalar)|scalar $aValue
     * @phpstan-param (callable(ArgType):scalar)|scalar $bValue
     */
    public function __construct($aValue, $bValue)
    {
        $this->aValue = $aValue;
        $this->bValue = $bValue;
    }

    public function __invoke(...$args): bool
    {
        return $this->evaluateArgument($this->aValue, ...$args) <=
            $this->evaluateArgument($this->bValue, ...$args);
    }
}
