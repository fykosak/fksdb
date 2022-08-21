<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Events;

use Nette\InvalidArgumentException;
use Nette\SmartObject;

class ExpressionPrinter
{
    use SmartObject;

    /**
     * @param mixed $expression
     */
    public function printExpression($expression): string
    {
        if (is_scalar($expression)) {
            return (string)$expression;
        } elseif (is_callable($expression)) {
            return (string)$expression;
        } else {
            throw new InvalidArgumentException("Cannot evaluate condition $expression.");
        }
    }
}
