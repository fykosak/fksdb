<?php

declare(strict_types=1);

namespace FKSDB\Models\Expressions\Logic;

use FKSDB\Models\Expressions\VariadicExpression;

class LogicOr extends VariadicExpression
{
    public function __invoke(...$args): bool
    {
        foreach ($this->arguments as $argument) {
            if ($this->evaluateArgument($argument, ...$args)) {
                return true;
            }
        }
        return false;
    }
}
