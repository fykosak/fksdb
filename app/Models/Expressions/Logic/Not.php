<?php

namespace FKSDB\Models\Expressions\Logic;

use FKSDB\Models\Expressions\EvaluatedExpression;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class Not extends EvaluatedExpression {

    /** @var mixed */
    private $expression;

    /**
     * Not constructor.
     * @param callable|mixed $expression
     */
    public function __construct($expression) {
        $this->expression = $expression;
    }

    /**
     * @param array $args
     * @return bool
     */
    final public function __invoke(...$args): bool {
        return !$this->evaluateArgument($this->expression, ...$args);
    }

    public function __toString(): string {
        return "!({$this->expression})";
    }

}
