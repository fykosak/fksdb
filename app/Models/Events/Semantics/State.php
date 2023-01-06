<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Semantics;

use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\Expressions\EvaluatedExpression;
use FKSDB\Models\Transitions\Holder\ModelHolder;

class State extends EvaluatedExpression
{
    private string $state;

    public function __construct(string $state)
    {
        $this->state = $state;
    }

    /**
     * @param $holder
     * @param ...$args
     */
    public function __invoke($holder, ...$args): bool
    {
        return $holder->getModelState()->value === $this->state;
    }

    public function __toString(): string
    {
        return "state == $this->state";
    }
}
