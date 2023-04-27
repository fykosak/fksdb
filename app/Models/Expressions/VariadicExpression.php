<?php

declare(strict_types=1);

namespace FKSDB\Models\Expressions;

abstract class VariadicExpression extends EvaluatedExpression
{
    protected array $arguments;

    public function __construct(...$args)
    {
        $this->arguments = $args;
    }
}
