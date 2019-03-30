<?php

namespace FKSDB\Expressions\Logic;

use FKSDB\Expressions\EvaluatedExpression;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class Not extends EvaluatedExpression {

    private $expression;

    /**
     * Not constructor.
     * @param $expression
     */
    function __construct($expression) {
        $this->expression = $expression;
    }

    /**
     * @return bool
     */
    public function __invoke() {
        $args = func_get_args();
        return !$this->evalArg($this->expression, $args);
    }

    /**
     * @return string
     */
    public function __toString() {
        return "!({$this->expression})";
    }

}
