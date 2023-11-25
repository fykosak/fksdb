<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Semantics;

use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\Transitions\Holder\ParticipantHolder;
use FKSDB\Models\Transitions\Statement;

/**
 * @implements Statement<bool,BaseHolder|ParticipantHolder>
 */
class State implements Statement
{
    private string $state;

    public function __construct(string $state)
    {
        $this->state = $state;
    }

    public function __invoke(...$args): bool
    {
        /** @var BaseHolder|ParticipantHolder $holder */
        [$holder] = $args;
        if ($holder instanceof BaseHolder) {
            return $holder->getModelState()->value === $this->state;
        } else {
            return $holder->getState()->value === $this->state;
        }
    }
}
