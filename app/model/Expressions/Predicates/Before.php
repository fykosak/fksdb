<?php

namespace FKSDB\Expressions\Predicates;

use FKSDB\Expressions\EvaluatedExpression;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class Before extends EvaluatedExpression {

    /** @var mixed */
    private $datetime;

    /**
     * Before constructor.
     * @param $datetime
     */
    function __construct($datetime) {
        $this->datetime = $datetime;
    }

    /**
     * @return bool
     */
    public function __invoke() {
        $datetime = $this->evalArg($this->datetime, func_get_args());
        return $datetime->getTimestamp() >= time();
    }

    /**
     * @return string
     */
    public function __toString() {
        return "now <= {$this->datetime}";
    }

}
