<?php

namespace Events\Model;

use Nette\InvalidArgumentException;
use Nette\Object;

/**
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class ExpressionEvaluator extends Object {

    public function evaluate($condition, $context) {
        if (is_scalar($condition)) {
            return $condition;
        } else if (is_callable($condition)) {
            return call_user_func($condition, $context);
        } else {
            throw new InvalidArgumentException("Cannot evaluate condition $condition.");
        }
    }

}
