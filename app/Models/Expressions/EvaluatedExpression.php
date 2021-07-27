<?php

namespace FKSDB\Models\Expressions;

use Nette\SmartObject;

abstract class EvaluatedExpression {
    use SmartObject;

    /**
     * @param mixed $evaluated
     * @param ...$args
     * @return mixed
     */
    final protected function evaluateArgument($evaluated, ...$args) {
        if (is_callable($evaluated)) {
            return $evaluated(...$args);
        } else {
            return $evaluated;
        }
    }

    /**
     * @param array ...$args
     * @return mixed
     */
    abstract public function __invoke(...$args);

}
