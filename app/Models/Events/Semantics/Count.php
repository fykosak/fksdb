<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Semantics;

use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\Transitions\Holder\ParticipantHolder;
use FKSDB\Models\Transitions\Statement;
use Nette\SmartObject;

/**
 * @implements Statement<int,BaseHolder|ParticipantHolder>
 */
class Count implements Statement
{
    use SmartObject;

    /** @phpstan-var string[] */
    private array $states;

    /**
     * @phpstan-param string[] $states
     */
    public function __construct(array $states)
    {
        $this->states = $states;
    }

    /**
     * @param BaseHolder|ParticipantHolder $args
     */
    public function __invoke(...$args): int
    {
        [$holder] = $args;
        $table = $holder->getModel()->event->getParticipants()->where('status', $this->states);
        return $table->count('1');
    }
}
