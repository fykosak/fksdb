<?php

namespace FKSDB\Expressions\Logic;

use FKSDB\Expressions\VariadicExpression;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class LogicOr extends VariadicExpression {

    /**
     * @param array $args
     * @return bool|mixed
     */
    protected function evaluate(...$args): bool {
        foreach ($this->arguments as $argument) {
            if ($this->evalArg($argument, ...$args)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return mixed|string
     */
    protected function getInfix() {
        return '||';
    }

}
