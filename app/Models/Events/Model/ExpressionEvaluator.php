<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Model;

use FKSDB\Models\Transitions\Holder\ModelHolder;
use Nette\InvalidArgumentException;
use Nette\SmartObject;

class ExpressionEvaluator
{
    use SmartObject;

    /**
     * @param callable|mixed $condition
     * @return mixed
     */
    public function evaluate($condition, ModelHolder $context)
    {
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
