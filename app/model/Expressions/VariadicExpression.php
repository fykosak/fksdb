<?php

namespace FKSDB\Expressions;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
abstract class VariadicExpression extends EvaluatedExpression {
    /**
     * @var callable[]|bool[]
     */
    protected $arguments;

    public function __construct(...$args) {
        $this->arguments = $args;
    }

    /**
     * @param mixed ...$args
     * @return mixed
     */
    public function __invoke(...$args): bool {
        return $this->evaluate(...$args);
    }

    /**
     * VariadicExpression constructor.
     * @param array ...$args
     */
    abstract protected function evaluate(...$args): bool;

    /**
     * @return string
     */
    public function __toString() {
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
