<?php

declare(strict_types=1);

namespace FKSDB\Models\Expressions;

use FKSDB\Models\Transitions\Statement;
use Nette\SmartObject;

/**
 * @template GlobalReturn
 * @template SubReturn
 * @template ArgType
 * @phpstan-implements Statement<GlobalReturn,ArgType>
 */
abstract class EvaluatedExpression implements Statement
{
    use SmartObject;

    /**
     * @phpstan-param (callable(ArgType):SubReturn)|SubReturn $evaluated
     * @phpstan-param ArgType $args
     * @phpstan-return SubReturn
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
