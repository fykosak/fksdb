<?php

namespace FKSDB\Models\Expressions\Logic;

use FKSDB\Models\Expressions\VariadicExpression;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class LogicAnd extends VariadicExpression {

    protected function evaluate(...$args): bool {
        foreach ($this->arguments as $argument) {
            if (!$this->evaluateArgument($argument, ...$args)) {
                return false;
            }
        }
        return true;
    }

    protected function getInfix(): string {
        return '&&';
    }
}
