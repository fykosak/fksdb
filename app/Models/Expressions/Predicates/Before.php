<?php

declare(strict_types=1);

namespace FKSDB\Models\Expressions\Predicates;

use FKSDB\Models\Expressions\EvaluatedExpression;
use Nette\InvalidStateException;

class Before extends EvaluatedExpression
{

    /** @var mixed */
    private $datetime;

    /**
     * Before constructor.
     * @param \DateTimeInterface|callable $datetime
     */
    public function __construct($datetime)
    {
        $this->datetime = $datetime;
    }

    /**
     * @param mixed $holder
     * @param ...$args
     */
    public function __invoke($holder, ...$args): bool
    {
        $datetime = $this->evaluateArgument($this->datetime, $holder);
        if (!$datetime instanceof \DateTimeInterface) {
            throw new InvalidStateException();
        }
        return $datetime->getTimestamp() >= time();
    }

    public function __toString(): string
    {
        return "now <= $this->datetime";
    }
}
