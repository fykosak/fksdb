<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Semantics;

use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\Expressions\EvaluatedExpression;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use Nette\SmartObject;

class State extends EvaluatedExpression
{
    use SmartObject;

    private string $state;

    public function __construct(string $state)
    {
        $this->state = $state;
    }

    /**
     * @param BaseHolder $holder
     */
    public function __invoke(ModelHolder $holder): bool
    {
        return $holder->getModelState() == $this->state;
    }

    public function __toString(): string
    {
        return "state == $this->state";
    }
}
