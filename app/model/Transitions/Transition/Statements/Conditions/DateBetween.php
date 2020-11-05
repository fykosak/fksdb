<?php

namespace FKSDB\Transitions\Statements\Conditions;

use DateTime;
use Exception;
use FKSDB\Transitions\Statements\Statement;

/**
 * Class DateBetween
 * @author Michal Červeňák <miso@fykos.cz>
 */
class DateBetween extends Statement {

    private \DateTimeInterface $to;

    private \DateTimeInterface $from;

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
     * @param array $args
     * @return bool
     */
    protected function evaluate(...$args): bool {
        return (\time() <= $this->to->getTimestamp()) && (\time() >= $this->from->getTimestamp());
    }

}
