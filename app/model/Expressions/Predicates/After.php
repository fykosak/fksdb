<?php

namespace FKSDB\Expressions\Predicates;

use FKSDB\Expressions\EvaluatedExpression;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class After extends EvaluatedExpression {

    /** @var mixed */
    private $datetime;

    /**
     * After constructor.
     * @param $datetime
     */
    function __construct($datetime) {
        $this->datetime = $datetime;
    }

    /**
     * @param array $args
     * @return bool
     */
    public function __invoke(...$args): bool {
        $datetime = $this->evaluateArgument($this->datetime, ...$args);
        return $datetime->getTimestamp() <= time();
    }

    /**
     * @return string
     */
    public function __toString() {
        return "now >= {$this->datetime}";
    }

}
