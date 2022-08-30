<?php

declare(strict_types=1);

namespace FKSDB\Models\Expressions\Logic;

use FKSDB\Models\Expressions\VariadicExpression;
use FKSDB\Models\Transitions\Holder\ModelHolder;

class LogicAnd extends VariadicExpression
{

    protected function evaluate(ModelHolder $holder, ...$args): bool
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
