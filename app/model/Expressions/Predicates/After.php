<?php

namespace FKSDB\Expressions\Predicates;

use FKSDB\Expressions\EvaluatedExpression;
use Nette\InvalidStateException;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class After extends EvaluatedExpression {

    /** @var mixed */
    private $datetime;

    /**
     * After constructor.
     * @param \DateTimeInterface|callable $datetime
     */
    public function __construct($datetime) {
        $this->datetime = $datetime;
    }

    /**
     * @param array $args
     * @return bool
     */
    public function __invoke(...$args): bool {
        $datetime = $this->evaluateArgument($this->datetime, ...$args);
        if (!$datetime instanceof \DateTimeInterface) {
            throw new InvalidStateException();
        }
        return $datetime->getTimestamp() <= time();
    }

    public function __toString(): string {
        return "now >= {$this->datetime}";
    }

}
