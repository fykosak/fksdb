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
    public function __construct($expression) {
        $this->expression = $expression;
    }

    /**
     * @param array $args
     * @return bool
     */
    public final function __invoke(...$args): bool {
        return !$this->evaluateArgument($this->expression, ...$args);
    }

    /**
     * @return string
     */
    public function __toString() {
        return "!({$this->expression})";
    }

}
