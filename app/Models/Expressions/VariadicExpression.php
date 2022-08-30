<?php

declare(strict_types=1);

namespace FKSDB\Models\Expressions;

use FKSDB\Models\Transitions\Holder\ModelHolder;

abstract class VariadicExpression extends EvaluatedExpression
{

    protected array $arguments;

    public function __construct(...$args)
    {
        $this->arguments = $args;
    }

    /**
     * @return mixed
     */
    final public function __invoke(ModelHolder $holder)
    {
        return $this->evaluate($holder);
    }

    /**
     * @return mixed
     */
    abstract protected function evaluate(ModelHolder $holder);

    abstract protected function getInfix(): string;

    public function __toString(): string
    {
        $terms = [];
        foreach ($this->arguments as $arg) {
            $terms[] = (string)$arg;
        }
        $result = implode(' ' . $this->getInfix() . ' ', $terms);
        if (count($terms) > 1) {
            $result = "($result)";
        }
        return $result;
    }
}
