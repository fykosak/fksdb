<?php


namespace FKSDB\Transitions\Statements\Conditions;


use DateTime;
use FKSDB\Transitions\IStateModel;
use FKSDB\Transitions\Statements\Statement;
use function time;

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
     * @param DateTime $to
     */
    public function __construct(DateTime $to) {
        $this->to = $to;
    }

    /**
     * @param IStateModel $model
     * @return bool
     */
    protected function evaluate(IStateModel $model = null): bool {
        return (time() <= $this->to->getTimestamp());
    }
}
