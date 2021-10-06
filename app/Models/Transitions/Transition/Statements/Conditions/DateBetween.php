<?php

namespace FKSDB\Models\Transitions\Transition\Statements\Conditions;

use FKSDB\Models\Transitions\Transition\Statements\Statement;

class DateBetween extends Statement {

    private \DateTimeInterface $to;
    private \DateTimeInterface $from;

    /**
     * @throws \Exception
     */
    public function __construct(string $from, string $to) {
        $this->from = new \DateTime($from);
        $this->to = new \DateTime($to);
    }

    protected function evaluate(...$args): bool {
        return (\time() <= $this->to->getTimestamp()) && (\time() >= $this->from->getTimestamp());
    }

}
