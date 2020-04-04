<?php

namespace FKSDB\Expressions;

use Nette\SmartObject;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
abstract class EvaluatedExpression {
    use SmartObject;

    /**
     * @param $evaluated
     * @param $args
     * @return mixed
     */
    protected final function evaluateArgument($evaluated, ...$args) {
        if (is_callable($evaluated)) {
            return $evaluated(...$args);
        } else {
            return $evaluated;
        }
    }

    /**
     * @param array ...$args
     * @return bool
     */
    public abstract function __invoke(...$args);

}
