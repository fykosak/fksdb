<?php

declare(strict_types=1);

namespace FKSDB\Models\Expressions;

use FKSDB\Models\Transitions\Statement;
use Nette\SmartObject;

/**
 * @template T,R,P
 * @phpstan-implements Statement<T,P>
 */
abstract class EvaluatedExpression implements Statement
{
    use SmartObject;

    /**
     * @param mixed $evaluated
     * @return mixed
     * @phpstan-return R
     */
    final protected function evaluateArgument($evaluated, ...$args)
    {
        if (is_callable($evaluated)) {
            return $evaluated(...$args);
        } else {
            return $evaluated;
        }
    }
}
