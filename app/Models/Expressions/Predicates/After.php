<?php

declare(strict_types=1);

namespace FKSDB\Models\Expressions\Predicates;

use FKSDB\Models\Expressions\EvaluatedExpression;
use Nette\InvalidStateException;

class After extends EvaluatedExpression
{

    /** @var mixed */
    private $datetime;

    /**
     * After constructor.
     * @param \DateTimeInterface|callable $datetime
     */
    public function __construct($datetime)
    {
        $this->datetime = $datetime;
    }

    /**
     * @param array $args
     * @return bool
     */
    public function __invoke(...$args): bool
    {
        $datetime = $this->evaluateArgument($this->datetime, ...$args);
        if (!$datetime instanceof \DateTimeInterface) {
            throw new InvalidStateException();
        }
        return $datetime->getTimestamp() <= time();
    }

    public function __toString(): string
    {
        return "now >= $this->datetime";
    }
}
