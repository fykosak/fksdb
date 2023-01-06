<?php

declare(strict_types=1);

namespace FKSDB\Models\Expressions;

abstract class VariadicExpression extends EvaluatedExpression
{
    protected array $arguments;

    public function __construct(...$args)
    {
        $this->arguments = $args;
    }

    /**
     * @param ...$args
     * @return mixed
     */
    final public function __invoke($holder, ...$args)
    {
        return $this->evaluate($holder, ...$args);
    }

    /**
     * @return mixed
     */
    abstract protected function evaluate($holder, ...$args);

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
