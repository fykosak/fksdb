<?php

declare(strict_types=1);

namespace FKSDB\Models\Expressions\Logic;

use FKSDB\Models\Expressions\EvaluatedExpression;

/**
 * @phpstan-extends EvaluatedExpression<bool,bool,ArgType>
 * @phpstan-template ArgType
 */
class Not extends EvaluatedExpression
{

    /** @phpstan-var (callable(ArgType):bool)|bool */
    private $expression;

    /** @phpstan-param (callable(ArgType):bool)|bool $expression */
    public function __construct($expression)
    {
        $this->expression = $expression;
    }

    final public function __invoke(...$args): bool
    {
        return !$this->evaluateArgument($this->expression, ...$args);
    }
}
