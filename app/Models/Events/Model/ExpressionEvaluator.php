<?php

namespace FKSDB\Models\Events\Model;

use Nette\InvalidArgumentException;
use Nette\SmartObject;

/**
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class ExpressionEvaluator {

    use SmartObject;

    /**
     * @param mixed $condition
     * @param mixed $context
     * @return mixed
     */
    public function evaluate($condition, $context) {
        if (is_scalar($condition)) {
            return $condition;
        } elseif (is_callable($condition)) {
            return $condition($context);
           // return call_user_func($condition, $context);
        } else {
            throw new InvalidArgumentException("Cannot evaluate condition $condition.");
        }
    }

}
