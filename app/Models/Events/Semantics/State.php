<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Semantics;

use FKSDB\Models\Transitions\Holder\ParticipantHolder;
use FKSDB\Models\Transitions\Statement;

/**
 * @implements Statement<bool,ParticipantHolder>
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
        /** @var ParticipantHolder $holder */
        [$holder] = $args;
        return $holder->getState()->value === $this->state;
    }
}
