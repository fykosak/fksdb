<?php

namespace FKSDB\Models\Expressions\Predicates;

use FKSDB\Models\Expressions\EvaluatedExpression;
use Nette\InvalidStateException;

class Before extends EvaluatedExpression
{

    /** @var \DateTimeInterface|callable */
    private $datetime;

    public function __construct(\DateTimeInterface|callable $datetime)
    {
        $this->datetime = $datetime;
    }

    public function __invoke(...$args): bool
    {
        $datetime = $this->evaluateArgument($this->datetime, ...$args);
        if (!$datetime instanceof \DateTimeInterface) {
            throw new InvalidStateException();
        }
        return $datetime->getTimestamp() >= time();
    }

    public function __toString(): string
    {
        return "now <= {$this->datetime}";
    }

}
