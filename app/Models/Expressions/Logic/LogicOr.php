<?php

declare(strict_types=1);

namespace FKSDB\Models\Expressions\Logic;

use FKSDB\Models\Expressions\VariadicExpression;

class LogicOr extends VariadicExpression
{
    /**
     * @param mixed $holder
     */
    protected function evaluate($holder, ...$args): bool
    {
        foreach ($this->arguments as $argument) {
            if ($this->evaluateArgument($argument, $holder, ...$args)) {
                return true;
            }
        }
        return false;
    }

    protected function getInfix(): string
    {
        return '||';
    }
}
