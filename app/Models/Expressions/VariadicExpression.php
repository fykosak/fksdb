<?php

declare(strict_types=1);

namespace FKSDB\Models\Expressions;

/**
 * @phpstan-extends EvaluatedExpression<bool,bool,ArgType>
 * @template ArgType
 */
abstract class VariadicExpression extends EvaluatedExpression
{
    protected array $arguments;

    public function __construct(...$args)
    {
        $this->arguments = $args;
    }
}
