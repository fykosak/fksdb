<?php

declare(strict_types=1);

namespace FKSDB\Models\Expressions\Logic;

use FKSDB\Models\Expressions\VariadicExpression;

class LogicAnd extends VariadicExpression
{
    /**
     * @param mixed $holder
     */
    protected function evaluate($holder, ...$args): bool
    {
        foreach ($this->arguments as $argument) {
            if (!$this->evaluateArgument($argument, $holder, ...$args)) {
                return false;
            }
        }
        return true;
    }

    protected function getInfix(): string
    {
        return '&&';
    }
}
