<?php

namespace FKSDB\Models\Expressions\Logic;

use FKSDB\Models\Expressions\VariadicExpression;

class LogicOr extends VariadicExpression {

    protected function evaluate(...$args): bool {
        foreach ($this->arguments as $argument) {
            if ($this->evaluateArgument($argument, ...$args)) {
                return true;
            }
        }
        return false;
    }

    protected function getInfix(): string {
        return '||';
    }
}
