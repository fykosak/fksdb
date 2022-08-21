<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Semantics;

use FKSDB\Models\Expressions\EvaluatedExpression;
use Nette\SmartObject;

class State extends EvaluatedExpression
{
    use SmartObject;
    use WithEventTrait;

    private string $state;

    public function __construct(string $state)
    {
        $this->state = $state;
    }

    public function __invoke(...$args): bool
    {
        return $this->getHolder($args[0])->primaryHolder->getModelState() == $this->state;
    }

    public function __toString(): string
    {
        return "state == $this->state";
    }
}
