<?php

namespace FKSDB\Components\Events;

use Nette\InvalidArgumentException;
use Nette\Object;

/**
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class ExpressionPrinter extends Object {

    public function printExpression($expression) {
        if (is_scalar($expression)) {
            return (string) $expression;
        } else if (is_callable($expression)) {
            return (string) $expression;
        } else {
            throw new InvalidArgumentException("Cannot evaluate condition $expression.");
        }
    }

}
