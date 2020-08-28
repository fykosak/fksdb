<?php

namespace FKSDB\Expressions;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
abstract class VariadicExpression extends EvaluatedExpression {

    protected array $arguments;

    /**
     * VariadicExpression constructor.
     * @param array ...$args
     */
    public function __construct(...$args) {
        $this->arguments = $args;
    }

    /**
     * @param mixed ...$args
     * @return mixed
     */
    final public function __invoke(...$args) {
        return $this->evaluate(...$args);
    }

    /**
     * @param mixed $args
     * @return mixed
     */
    abstract protected function evaluate(...$args);

    abstract protected function getInfix(): string;

    public function __toString(): string {
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
