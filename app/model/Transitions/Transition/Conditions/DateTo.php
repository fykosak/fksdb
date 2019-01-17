<?php


namespace FKSDB\Transitions\Conditions;


use FKSDB\Transitions\IStateModel;
use FKSDB\Transitions\Statement;

class DateTo extends Statement {
    /**
     * @var \DateTime
     */
    private $to;

    /**
     * DateBetween constructor.
     * @param \DateTime $to
     */
    public function __construct(\DateTime $to) {
        $this->to = $to;
    }

    /**
     * @param IStateModel $model
     * @return bool
     */
    protected function evaluate(IStateModel $model = null): bool {
        return (\time() <= $this->to->getTimestamp());
    }
}
