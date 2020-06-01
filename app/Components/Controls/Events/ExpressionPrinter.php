<?php

namespace FKSDB\Components\Events;

use Nette\InvalidArgumentException;
use Nette\SmartObject;

/**
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class ExpressionPrinter {
    use SmartObject;

    /**
     * @param mixed $expression
     * @return string
     */
    public function printExpression($expression): string {
        if (is_scalar($expression)) {
            return (string)$expression;
        } elseif (is_callable($expression)) {
            return (string)$expression;
        } else {
            throw new InvalidArgumentException("Cannot evaluate condition $expression.");
        }
    }
}
