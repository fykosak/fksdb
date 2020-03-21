<?php

namespace FKSDB\Transitions\Statements\Conditions;

use DateTime;
use Exception;
use FKSDB\Transitions\IStateModel;
use FKSDB\Transitions\Statements\Statement;
use function time;

/**
 * Class DateBetween
 * @package FKSDB\Transitions\Statements\Conditions
 */
class DateBetween extends Statement {
    /**
     * @var DateTime
     */
    private $to;
    /**
     * @var DateTime
     */
    private $from;

    /**
     * DateBetween constructor.
     * @param string $from
     * @param string $to
     * @throws Exception
     */
    public function __construct(string $from, string $to) {
        $this->from = new DateTime($from);
        $this->to = new DateTime($to);
    }

    /**
     * @param IStateModel $model
     * @return bool
     */
    protected function evaluate(IStateModel $model = null): bool {
        return (time() <= $this->to->getTimestamp()) && (time() >= $this->from->getTimestamp());
    }

}
