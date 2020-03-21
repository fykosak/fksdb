<?php

namespace FKSDB\Transitions\Statements\Conditions;

use DateTime;
use FKSDB\Transitions\IStateModel;
use FKSDB\Transitions\Statements\Statement;

/**
 * Class DateTo
 * @package FKSDB\Transitions\Statements\Conditions
 */
class DateTo extends Statement {
    /**
     * @var DateTime
     */
    private $to;

    /**
     * DateBetween constructor.
     * @param string $to
     * @throws \Exception
     */
    public function __construct(string $to) {
        $this->to = new DateTime($to);
    }

    /**
     * @param IStateModel $model
     * @return bool
     */
    protected function evaluate(IStateModel $model = null): bool {
        return (\time() <= $this->to->getTimestamp());
    }
}
