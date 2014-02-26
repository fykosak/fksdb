<?php

namespace FKS\Expressions\Logic;

use FKS\Expressions\VariadicExpression;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class Or_ extends VariadicExpression {

    protected function evaluate($args) {
        for ($i = 0; $i < $this->getArity(); ++$i) {
            if ($this->evalArgAt($i, $args)) {
                return true;
            }
        }
        return false;
    }

    protected function getInfix() {
        return '||';
    }

}
