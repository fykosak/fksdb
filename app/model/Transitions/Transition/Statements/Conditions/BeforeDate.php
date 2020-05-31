<?php

namespace FKSDB\Transitions\Statements\Conditions;

use DateTime;
use FKSDB\Transitions\Statements\Statement;

/**
 * Class BeforeDate
 * @author Michal Červeňák <miso@fykos.cz>
 */
class BeforeDate extends Statement {

    private \DateTimeInterface $to;

    /**
     * DateBetween constructor.
     * @param string $to
     * @throws \Exception
     */
    public function __construct(string $to) {
        $this->to = new DateTime($to);
    }

    /**
     * @param array $args
     * @return bool
     */
    protected function evaluate(...$args): bool {
        return (\time() <= $this->to->getTimestamp());
    }
}
