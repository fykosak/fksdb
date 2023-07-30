<?php

declare(strict_types=1);

namespace FKSDB\Models\Expressions;

use FKSDB\Models\Transitions\Statement;
use Nette\SmartObject;

/**
 * @phpstan-template T
 * @phpstan-template R
 * @phpstan-template P
 * @phpstan-implements Statement<T,P>
 */
abstract class EvaluatedExpression implements Statement
{
    use SmartObject;

    /**
     * @phpstan-param callable(P):R|R $evaluated
     * @return mixed
     * @phpstan-param P $args
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
