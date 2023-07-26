<?php

declare(strict_types=1);

namespace FKSDB\Models\Expressions;

use FKSDB\Models\Transitions\Statement;
use Nette\SmartObject;

/**
 * @phpstan-template T
 * @implements Statement<T>
 */
abstract class EvaluatedExpression implements Statement
{
    use SmartObject;

    /**
     * @param mixed $evaluated
     * @return mixed
     * @phpstan-return T
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
