<?php

declare(strict_types=1);

namespace FKSDB\Models\Expressions;

use Nette\SmartObject;

abstract class EvaluatedExpression
{
    use SmartObject;

    /**
     * @param mixed $evaluated
     * @return mixed
     */
    final protected function evaluateArgument($evaluated, ...$args)
    {
        if (is_callable($evaluated)) {
            return $evaluated(...$args);
        } else {
            return $evaluated;
        }
    }

    /**
     * @return mixed
     */
    abstract public function __invoke(...$args);
}
