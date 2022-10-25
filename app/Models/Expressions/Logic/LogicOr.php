<?php

declare(strict_types=1);

namespace FKSDB\Models\Expressions\Logic;

use FKSDB\Models\Expressions\VariadicExpression;
use FKSDB\Models\Transitions\Holder\ModelHolder;

class LogicOr extends VariadicExpression
{

    protected function evaluate(ModelHolder $holder): bool
    {
        foreach ($this->arguments as $argument) {
            if ($this->evaluateArgument($argument, $holder)) {
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
