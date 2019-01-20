<?php

namespace FKSDB\Transitions\Statements\Conditions;

use FKSDB\Transitions\IStateModel;
use FKSDB\Transitions\Statements\Statement;

class DateBetween extends Statement {
    /**
     * @var \DateTime
     */
    private $to;
    /**
     * @var \DateTime
     */
    private $from;

    /**
     * DateBetween constructor.
     * @param \DateTime $from
     * @param \DateTime $to
     */
    public function __construct(\DateTime $from, \DateTime $to) {
        $this->from = $from;
        $this->to = $to;
    }

    /**
     * @param IStateModel $model
     * @return bool
     */
    protected function evaluate(IStateModel $model = null): bool {
        return (\time() <= $this->to->getTimestamp()) && (\time() >= $this->from->getTimestamp());
    }

}
