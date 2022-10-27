<?php

declare(strict_types=1);

namespace FKSDB\Models\Expressions\Logic;

use FKSDB\Models\Expressions\EvaluatedExpression;
use FKSDB\Models\Transitions\Holder\ModelHolder;

class Not extends EvaluatedExpression
{

    /** @var mixed */
    private $expression;

    /**
     * Not constructor.
     * @param callable|mixed $expression
     */
    public function __construct($expression)
    {
        $this->expression = $expression;
    }

    final public function __invoke(ModelHolder $holder): bool
    {
        return !$this->evaluateArgument($this->expression, $holder);
    }

    public function __toString(): string
    {
        return "!($this->expression)";
    }
}
