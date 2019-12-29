<?php

namespace FKSDB\Transitions\Statements\Conditions;

use DateTime;
use FKSDB\Transitions\IStateModel;
use FKSDB\Transitions\Statements\Statement;

/**
 * Class DateFrom
 * @package FKSDB\Transitions\Statements\Conditions
 */
class DateFrom extends Statement {
    /**
     * @var DateTime
     */
    private $from;

    /**
     * DateBetween constructor.
     * @param DateTime $from
     */
    public function __construct(DateTime $from) {
        $this->from = $from;
    }

    /**
     * @param IStateModel $model
     * @return bool
     */
    protected function evaluate(IStateModel $model = null): bool {
        return (\time() >= $this->from->getTimestamp());
    }
}
