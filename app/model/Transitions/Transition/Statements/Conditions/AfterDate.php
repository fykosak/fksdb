<?php

namespace FKSDB\Transitions\Statements\Conditions;

use DateTime;
use FKSDB\Transitions\Statements\Statement;

/**
 * Class DateFrom
 * *
 */
class AfterDate extends Statement {
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
     * @param array $args
     * @return bool
     */
    protected function evaluate(...$args): bool {
        return (\time() >= $this->from->getTimestamp());
    }
}
