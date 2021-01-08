<?php

namespace FKSDB\Models\Expressions;

use Nette\SmartObject;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
abstract class EvaluatedExpression {
    use SmartObject;

    /**
     * @param mixed $evaluated
     * @param mixed $args
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
