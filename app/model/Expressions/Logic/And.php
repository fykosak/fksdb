<?php

namespace FKSDB\Expressions\Logic;

use FKSDB\Expressions\VariadicExpression;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class And_ extends VariadicExpression {

    /**
     * @param $args
     * @return bool|mixed
     */
    protected function evaluate($args) {
        for ($i = 0; $i < $this->getArity(); ++$i) {
            if (!$this->evalArgAt($i, $args)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @return mixed|string
     */
    protected function getInfix() {
        return '&&';
    }

}
