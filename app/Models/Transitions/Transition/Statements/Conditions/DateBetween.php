<?php

declare(strict_types=1);

namespace FKSDB\Models\Transitions\Transition\Statements\Conditions;

use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Statement;

class DateBetween implements Statement
{
    private \DateTimeInterface $to;
    private \DateTimeInterface $from;

    /**
     * @throws \Exception
     */
    public function __construct(string $from, string $to)
    {
        $this->from = new \DateTime($from);
        $this->to = new \DateTime($to);
    }

    /**
     * @param mixed $holder
     */
    public function __invoke($holder, ...$args): bool
    {
        return (\time() <= $this->to->getTimestamp()) && (\time() >= $this->from->getTimestamp());
    }
}
