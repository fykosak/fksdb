<?php

namespace FKSDB\Models\Transitions\Transition\Statements\Conditions;

use FKSDB\Models\Transitions\Transition\Statements\Statement;

class DateBetween extends Statement
{

    private \DateTime $to;

    private \DateTime $from;

    /**
     * DateBetween constructor.
     * @param string $from
     * @param string $to
     * @throws \Exception
     */
    public function __construct(string $from, string $to)
    {
        $this->from = new \DateTime($from);
        $this->to = new \DateTime($to);
    }

    /**
     * @param array $args
     * @return bool
     */
    protected function evaluate(...$args): bool
    {
        return (\time() <= $this->to->getTimestamp()) && (\time() >= $this->from->getTimestamp());
    }
}
