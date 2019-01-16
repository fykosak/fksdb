<?php

namespace FKSDB\Transitions\Conditions;

use FKSDB\Transitions\IStateModel;

class DateBetween extends AbstractCondition {
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
    public function evaluate($model = null): bool {
        return (\time() <= $this->to->getTimestamp()) && (\time() >= $this->from->getTimestamp());
    }

}
