<?php

declare(strict_types=1);

namespace FKSDB\Models\Expressions\Comparison;

use FKSDB\Models\Expressions\EvaluatedExpression;

/**
 * @template ArgType
 * @phpstan-extends EvaluatedExpression<bool,scalar,ArgType>
 */
class Le extends EvaluatedExpression
{

    /** @var (callable(ArgType):scalar)|scalar */
    private $aValue;
    /** @var (callable(ArgType):scalar)|scalar */
    private $bValue;

    /**
     * @param (callable(ArgType):scalar)|scalar $aValue
     * @param (callable(ArgType):scalar)|scalar $bValue
     */
    public function __construct($aValue, $bValue)
    {
        $this->aValue = $aValue;
        $this->bValue = $bValue;
    }

    public function __invoke(...$args): bool
    {
        return $this->evaluateArgument($this->aValue, ...$args) <
            $this->evaluateArgument($this->bValue, ...$args);
    }
}
