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
     * @param string $from
     * @throws \Exception
     */
    public function __construct(string $from) {
        $this->from = new DateTime($from);
    }

    /**
     * @param IStateModel $model
     * @param array $args
     * @return bool
     */
    protected function evaluate(IStateModel $model = null, ...$args): bool {
        return (\time() >= $this->from->getTimestamp());
    }
}
