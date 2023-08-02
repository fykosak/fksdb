<?php

declare(strict_types=1);

namespace FKSDB\Models\Expressions\Logic;

use FKSDB\Models\Expressions\VariadicExpression;

/**
 * @template ArgType
 * @phpstan-extends VariadicExpression<ArgType>
 */
class LogicAnd extends VariadicExpression
{
    public function __invoke(...$args): bool
    {
        foreach ($this->arguments as $argument) {
            if (!$this->evaluateArgument($argument, ...$args)) {
                return false;
            }
        }
        return true;
    }
}
